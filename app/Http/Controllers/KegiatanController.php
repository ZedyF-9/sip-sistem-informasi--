<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PesertaPkl;
use App\Services\PembacaExcelPkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $kegiatans = Kegiatan::query()
            ->withCount('sertifikats')
            ->withCount('pesertaPkls')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('kegiatan.index', compact('kegiatans', 'q'));
    }

    public function create()
    {
        return view('kegiatan.form', [
            'kegiatan' => new Kegiatan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $kegiatan = new Kegiatan($data);
        $kegiatan->created_by = $request->user()->id;
        $kegiatan->save();

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Kegiatan berhasil dibuat.');
    }

    public function show(Request $request, Kegiatan $kegiatan)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:berlangsung,selesai'],
        ]);
        // Keep old NIS/NISN search links working while using one broad search box.
        $q = trim(implode(' ', array_filter([
            trim($filters['q'] ?? ''),
            trim($filters['nisn'] ?? ''),
        ], fn ($value) => $value !== '')));
        $kelas = trim($filters['kelas'] ?? '');
        $jurusan = trim($filters['jurusan'] ?? '');
        $status = $filters['status'] ?? '';
        $terms = preg_split('/\s+/u', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $peserta = $kegiatan->pesertaPkls()
            ->when($terms !== [], function ($query) use ($terms) {
                // Every word must match somewhere, while a word may match any
                // participant field. This supports mixed searches such as
                // "TKJ 1 Budi" or "NIS Industri".
                foreach ($terms as $term) {
                    $query->where(function ($matches) use ($term) {
                        $pattern = '%'.$term.'%';
                        foreach ([
                            'nis', 'nama', 'kelas', 'jurusan', 'nama_industri', 'status',
                        ] as $column) {
                            $matches->orWhereRaw('LOWER('.$column.') LIKE ?', [$pattern]);
                        }
                    });
                }
            })
            ->when($kelas !== '', fn ($query) => $query
                ->whereRaw('LOWER(kelas) LIKE ?', ['%'.mb_strtolower($kelas).'%']))
            ->when($jurusan !== '', fn ($query) => $query
                ->whereRaw('LOWER(jurusan) LIKE ?', ['%'.mb_strtolower($jurusan).'%']))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->orderByRaw(
                'CASE WHEN LOWER(nis) = ? THEN 0 WHEN LOWER(nis) LIKE ? THEN 1 WHEN LOWER(nama) = ? THEN 2 WHEN LOWER(nama) LIKE ? THEN 3 WHEN LOWER(kelas) = ? THEN 4 ELSE 5 END',
                [mb_strtolower($q), mb_strtolower($q).'%', mb_strtolower($q), mb_strtolower($q).'%', mb_strtolower($q)]
            ))
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();
        $certificates = $kegiatan->sertifikats()
            ->latest('id')
            ->get()
            ->groupBy('kode_peserta')
            ->map(fn ($group) => $group->firstWhere('status', 'aktif') ?? $group->first());
        $peserta->getCollection()->each(function ($item) use ($certificates) {
            $item->setRelation('sertifikat', $certificates->get($item->nis));
        });
        $counts = [
            'peserta' => $kegiatan->pesertaPkls()->count(),
            'selesai' => $kegiatan->pesertaPkls()->where('status', 'selesai')->count(),
            'sertifikat' => $kegiatan->sertifikats()->where('status', 'aktif')->count(),
        ];
        $kelasList = $kegiatan->pesertaPkls()
            ->where('status', 'selesai')
            ->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $semuaKelas = $kegiatan->pesertaPkls()->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $kelasSertifikatList = $kegiatan->pesertaPkls()
            ->whereIn('nis', $kegiatan->sertifikats()
                ->where('status', 'aktif')
                ->whereNotNull('file_pdf')
                ->select('kode_peserta'))
            ->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $jurusanFilterList = $kegiatan->pesertaPkls()
            ->where('jurusan', '!=', '')
            ->select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');
        return view('kegiatan.show', compact(
            'kegiatan', 'peserta', 'counts', 'q', 'kelas', 'jurusan', 'status',
            'kelasList', 'kelasSertifikatList', 'jurusanFilterList', 'semuaKelas'
        ));
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.form', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($kegiatan, $data) {
            $target = Kegiatan::query()
                ->lockForUpdate()
                ->findOrFail($kegiatan->id);

            if ($target->sertifikats()->exists() || $target->pesertaPkls()->exists()) {
                throw ValidationException::withMessages([
                    'kegiatan' => 'Periode PKL yang sudah memiliki peserta tidak dapat diubah.',
                ]);
            }

            $target->update($data);
        });

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Kegiatan diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['lokasi'] = 'SMK Negeri 4 Tanjungpinang';
        return $data;
    }

    public function previewImport(Request $request, Kegiatan $kegiatan, PembacaExcelPkl $reader)
    {
        $data = $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:10240'],
        ], [
            'file_excel.mimes' => 'Unggah file Excel .xlsx.',
            'file_excel.extensions' => 'Ekstensi file harus .xlsx.',
            'file_excel.max' => 'Ukuran Excel maksimal 10 MB.',
        ]);

        $rows = $reader->baca($data['file_excel']->getRealPath());
        $existing = $kegiatan->pesertaPkls()->pluck('nis')->map(fn ($nis) => (string) $nis)->all();
        $seen = [];
        $errors = [];
        $validRows = [];
        foreach ($rows as $row) {
            $issues = [];
            foreach (['nis' => 'NIS', 'nama' => 'Nama lengkap', 'kelas' => 'Kelas', 'industri' => 'Industri'] as $key => $label) {
                if ($row[$key] === '') {
                    $issues[] = $label.' wajib diisi.';
                }
            }
            if ($row['tempat_lahir'] === '') {
                $issues[] = 'Tempat lahir wajib diisi.';
            }
            if ($row['tanggal_lahir'] === null) {
                $issues[] = 'Tanggal lahir wajib diisi.';
            }
            if ($row['nis'] !== '' && (! preg_match('/^[A-Za-z0-9._-]+$/', $row['nis']) || strlen($row['nis']) > 50)) {
                $issues[] = 'NIS hanya boleh memakai huruf, angka, titik, garis bawah, atau tanda minus (maksimal 50 karakter).';
            }
            if (mb_strlen($row['nama']) > 100 || mb_strlen($row['kelas']) > 100) {
                $issues[] = 'Nama maksimal 100 karakter dan kelas maksimal 100 karakter.';
            }
            if (mb_strlen($row['tempat_lahir']) > 100) {
                $issues[] = 'Tempat lahir maksimal 100 karakter.';
            }
            if ($row['nis'] !== '' && isset($seen[$row['nis']])) {
                $issues[] = 'NIS duplikat di dalam file.';
            }
            $seen[$row['nis']] = true;

            if ($issues === []) {
                $validRows[] = $row + [
                    'nama_industri' => $row['industri'],
                    'is_update' => in_array($row['nis'], $existing, true),
                ];
            } else {
                $errors[] = ['baris' => $row['baris'], 'nis' => $row['nis'], 'nama' => $row['nama'], 'pesan' => implode(' ', $issues)];
            }
        }

        $preview = [
            'rows' => $validRows,
            'errors' => $errors,
            'total' => count($rows),
        ];
        if ($errors === []) {
            session()->put('pkl_import_preview_'.$kegiatan->id, $validRows);
        } else {
            session()->forget('pkl_import_preview_'.$kegiatan->id);
        }

        return view('kegiatan.import-preview', compact('kegiatan', 'preview'));
    }

    public function importStudents(Request $request, Kegiatan $kegiatan)
    {
        $rows = session()->get('pkl_import_preview_'.$kegiatan->id);
        abort_unless(is_array($rows) && count($rows) > 0, 409, 'Pratinjau impor sudah kedaluwarsa. Unggah kembali file Excel.');

        $data = $request->validate([
            'kelas' => ['required', 'array', 'min:1'],
            'kelas.*' => ['required', 'string', 'max:100'],
        ], ['kelas.required' => 'Pilih minimal satu kelas untuk diimpor.']);
        $kelasDipilih = array_unique($data['kelas']);
        $kelasTersedia = array_unique(array_column($rows, 'kelas'));
        abort_if(array_diff($kelasDipilih, $kelasTersedia), 422, 'Pilihan kelas tidak sesuai dengan pratinjau impor.');
        $rows = array_values(array_filter($rows, fn ($row) => in_array($row['kelas'], $kelasDipilih, true)));

        $hasilImpor = DB::transaction(function () use ($kegiatan, $rows) {
            $jumlahBaru = 0;
            $jumlahDataLahir = 0;

            foreach ($rows as $row) {
                $peserta = $kegiatan->pesertaPkls()
                    ->where('nis', $row['nis'])
                    ->first();

                if ($peserta !== null) {
                    $dataLahir = [];
                    if (
                        $row['tempat_lahir'] !== ''
                        && $row['tempat_lahir'] !== $peserta->tempat_lahir
                    ) {
                        $dataLahir['tempat_lahir'] = $row['tempat_lahir'];
                    }
                    if (
                        $row['tanggal_lahir'] !== null
                        && $row['tanggal_lahir'] !== $peserta->tanggal_lahir
                    ) {
                        $dataLahir['tanggal_lahir'] = $row['tanggal_lahir'];
                    }

                    if ($dataLahir !== []) {
                        $peserta->update($dataLahir);
                        $jumlahDataLahir++;
                    }

                    continue;
                }

                PesertaPkl::create([
                    'kegiatan_id' => $kegiatan->id,
                    'nis' => $row['nis'],
                    'nama' => $row['nama'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tanggal_lahir' => $row['tanggal_lahir'],
                    'kelas' => $row['kelas'],
                    'jurusan' => $row['jurusan'],
                    'nama_industri' => $row['nama_industri'],
                ]);
                $jumlahBaru++;
            }

            return [
                'baru' => $jumlahBaru,
                'data_lahir' => $jumlahDataLahir,
            ];
        });

        session()->forget('pkl_import_preview_'.$kegiatan->id);
        return redirect()->route('tu.kegiatan.show', $kegiatan)
            ->with('status', $hasilImpor['baru'].' siswa baru diimpor, '
                .$hasilImpor['data_lahir'].' data tempat/tanggal lahir diperbarui.');
    }

    public function completeParticipant(Request $request, Kegiatan $kegiatan, PesertaPkl $pesertaPkl)
    {
        abort_unless((int) $pesertaPkl->kegiatan_id === (int) $kegiatan->id, 404);
        if ($kegiatan->tanggal_selesai->isFuture()) {
            throw ValidationException::withMessages([
                'status' => 'Siswa baru dapat ditandai selesai setelah periode PKL berakhir.',
            ]);
        }
        $pesertaPkl->update(['status' => 'selesai']);
        return back()
            ->withFragment('peserta-'.$pesertaPkl->id)
            ->with('status', 'Status PKL '.$pesertaPkl->nama.' diperbarui menjadi selesai.');
    }

    public function completeClass(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate(['kelas' => ['required', 'string', 'max:100']]);
        $jumlah = $kegiatan->pesertaPkls()->where('kelas', $data['kelas'])
            ->where('status', 'berlangsung')->update(['status' => 'selesai', 'updated_at' => now()]);
        return back()
            ->withFragment('form-selesaikan-kelas')
            ->with('status', $jumlah
                ? $jumlah.' siswa kelas '.$data['kelas'].' ditandai selesai.'
                : 'Tidak ada siswa berlangsung pada kelas tersebut.');
    }

    public function removeParticipant(Kegiatan $kegiatan, PesertaPkl $pesertaPkl)
    {
        abort_unless((int) $pesertaPkl->kegiatan_id === (int) $kegiatan->id, 404);
        abort_if(
            $kegiatan->sertifikats()->where('kode_peserta', $pesertaPkl->nis)->exists(),
            409,
            'Siswa yang sudah memiliki sertifikat tidak dapat dihapus dari periode ini.'
        );
        $pesertaPkl->delete();

        return back()->with('status', 'Data siswa PKL dihapus. Baris yang benar dapat diimpor kembali.');
    }

}
