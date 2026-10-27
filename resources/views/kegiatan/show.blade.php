@extends('layouts.internal')

@section('title', 'Kelola Periode PKL')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.kegiatan.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3">Kembali ke daftar PKL</a>
        @if ($counts['sertifikat'] > 0)
            <a href="{{ route('tu.kegiatan.sertifikat.zip', $kegiatan) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">Unduh Semua Sertifikat (ZIP)</a>
        @endif
    </div>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">{{ $kegiatan->nama }}</h2>
                <p class="mt-2 text-sm text-slate-600">Periode {{ $kegiatan->tanggal_mulai->format('d/m/Y') }} – {{ $kegiatan->tanggal_selesai->format('d/m/Y') }}</p>
                @if ($kegiatan->deskripsi)<p class="mt-3 whitespace-pre-line text-sm text-slate-600">{{ $kegiatan->deskripsi }}</p>@endif
            </div>
            @if ($counts['peserta'] === 0)
                <a href="{{ route('tu.kegiatan.edit', $kegiatan) }}" class="rounded-xl border border-slate-300 px-4 py-2">Edit Periode</a>
            @endif
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4"><p class="text-sm text-slate-500">Siswa diimpor</p><p class="mt-1 text-2xl font-bold">{{ $counts['peserta'] }}</p></div>
            <div class="rounded-xl bg-slate-50 p-4"><p class="text-sm text-slate-500">PKL selesai</p><p class="mt-1 text-2xl font-bold">{{ $counts['selesai'] }}</p></div>
            <div class="rounded-xl bg-slate-50 p-4"><p class="text-sm text-slate-500">Sertifikat aktif</p><p class="mt-1 text-2xl font-bold">{{ $counts['sertifikat'] }}</p></div>
        </div>
    </section>

    <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold">Impor Daftar Siswa dari Excel</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">
            Gunakan file .xlsx dengan kolom <code>nis</code>, <code>nama_lengkap</code>, <code>tempat_lahir</code>, <code>tanggal_lahir</code>, <code>kelas</code>, dan <code>industri</code>. Tempat dan tanggal lahir wajib diisi agar tercetak pada sertifikat.
            Jika NIS sudah terdaftar, impor hanya memperbarui data lahir dan mempertahankan data lainnya.
            Nama industri diambil langsung dari kolom Excel dan tidak perlu terdaftar sebagai mitra kerja sama.
        </p>
        <form action="{{ route('tu.kegiatan.import.preview', $kegiatan) }}" method="POST"
              enctype="multipart/form-data" class="mt-5 flex flex-wrap items-end gap-3">
            @csrf
            <div class="min-w-64 flex-1">
                <label for="file_excel" class="mb-2 block text-sm font-semibold">File Excel (.xlsx, maksimal 10 MB)</label>
                <input id="file_excel" name="file_excel" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                       required class="w-full rounded-xl border border-slate-300 p-3">
            </div>
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">Periksa dan Pratinjau</button>
        </form>
    </section>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold">Daftar Siswa PKL</h2>
            <p class="mt-1 text-sm text-slate-500">{{ number_format($peserta->total(), 0, ',', '.') }} {{ $q !== '' || $kelas !== '' || $jurusan !== '' || $status !== '' ? 'siswa ditemukan' : 'siswa terdaftar' }}. Tandai selesai setelah siswa menyelesaikan PKL.</p>
        </div>
    </div>

    <section aria-labelledby="cari-peserta-title" class="mb-5 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <h3 id="cari-peserta-title" class="font-bold text-slate-900">Cari peserta PKL</h3>
            <p class="mt-1 text-sm text-slate-500">Masukkan NIS, nama, kelas, jurusan, industri, atau status PKL.</p>
        </div>

        <form method="GET" action="{{ route('tu.kegiatan.show', $kegiatan) }}" role="search" class="px-5 py-5 sm:px-6">
            <label for="peserta-q" class="mb-2 block text-sm font-semibold text-slate-700">Kata kunci</label>
            <div class="relative">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400">
                        <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"></circle>
                        <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                    <input id="peserta-q" name="q" value="{{ $q }}" maxlength="200" autocomplete="off"
                           placeholder="Contoh: 009812 Dinda XII TKJ 1"
                           class="w-full rounded-lg border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15">
            </div>
            <p class="mt-2 text-xs text-slate-500">Boleh masukkan beberapa kata sekaligus. Semua kata akan dicari pada data siswa.</p>

            <details class="group mt-4 border-t border-slate-100 pt-3" @if ($kelas !== '' || $jurusan !== '' || $status !== '') open @endif>
                <summary class="flex cursor-pointer list-none items-center gap-2 py-1 text-sm font-semibold text-slate-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-slate-500"><path fill-rule="evenodd" d="M3 4.75A.75.75 0 0 1 3.75 4h12.5a.75.75 0 0 1 .53 1.28l-4.9 4.9a.75.75 0 0 0-.22.53v3.04a.75.75 0 0 1-.42.67l-2 1A.75.75 0 0 1 8.17 14.75v-4.04a.75.75 0 0 0-.22-.53l-4.9-4.9A.75.75 0 0 1 3 4.75Z" clip-rule="evenodd"/></svg>
                    Filter tambahan
                    <span class="font-normal text-slate-500">(kelas, jurusan, status)</span>
                    <span class="ml-auto text-xs font-medium text-slate-500 group-open:hidden">Tampilkan</span>
                    <span class="ml-auto hidden text-xs font-medium text-slate-500 group-open:inline">Sembunyikan</span>
                </summary>
                <div class="grid gap-3 pb-1 pt-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="peserta-kelas" class="mb-1.5 block text-xs font-semibold text-slate-600">Kelas</label>
                        <select id="peserta-kelas" name="kelas" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15">
                            <option value="">Semua kelas</option>
                            @foreach ($semuaKelas as $opsiKelas)
                                <option value="{{ $opsiKelas }}" @selected($kelas === $opsiKelas)>{{ $opsiKelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="peserta-jurusan" class="mb-1.5 block text-xs font-semibold text-slate-600">Jurusan</label>
                        <select id="peserta-jurusan" name="jurusan" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15">
                            <option value="">Semua jurusan</option>
                            @foreach ($jurusanFilterList as $opsiJurusan)
                                <option value="{{ $opsiJurusan }}" @selected($jurusan === $opsiJurusan)>{{ $opsiJurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="peserta-status" class="mb-1.5 block text-xs font-semibold text-slate-600">Status PKL</label>
                        <select id="peserta-status" name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15">
                            <option value="">Semua status</option>
                            <option value="berlangsung" @selected($status === 'berlangsung')>Berlangsung</option>
                            <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                        </select>
                    </div>
                </div>
            </details>

            @if ($kelas !== '' || $jurusan !== '' || $status !== '')
                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-xs text-slate-600">
                    <span class="font-semibold">Filter aktif:</span>
                    @if ($kelas !== '')<span class="rounded bg-slate-100 px-2 py-1">Kelas {{ $kelas }}</span>@endif
                    @if ($jurusan !== '')<span class="rounded bg-slate-100 px-2 py-1">{{ $jurusan }}</span>@endif
                    @if ($status !== '')<span class="rounded bg-slate-100 px-2 py-1">{{ ucfirst($status) }}</span>@endif
                    <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="ml-auto font-semibold text-blue-800 underline decoration-blue-300 underline-offset-2">Hapus semua</a>
                </div>
            @elseif ($q !== '')
                <div class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-600">
                    Pencarian: <span class="font-semibold text-slate-900">{{ $q }}</span>
                    <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="ml-2 font-semibold text-blue-800 underline decoration-blue-300 underline-offset-2">Hapus pencarian</a>
                </div>
            @endif

            <div class="mt-4 flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="px-2 py-2 text-sm font-semibold text-slate-600 underline decoration-slate-300 underline-offset-2 hover:text-slate-900">Reset</a>
                    <button class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-800 focus:ring-offset-2">
                        Tampilkan hasil
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section aria-labelledby="sertifikat-kelas-title" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="mb-4">
            <h3 id="sertifikat-kelas-title" class="font-bold text-slate-900">Sertifikat per kelas</h3>
            <p class="mt-1 text-sm text-slate-500">Pilihan kelas dibaca dari Excel yang sudah diimpor. Penerbitan hanya mencakup siswa berstatus selesai.</p>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <form action="{{ route('tu.kegiatan.kelas.complete', $kegiatan) }}" method="POST" class="rounded-lg bg-slate-50 p-4">
                @csrf
                @method('PATCH')
                <label for="selesai-kelas" class="mb-2 block text-sm font-semibold text-slate-700">Tandai PKL selesai sekaligus per kelas</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select id="selesai-kelas" name="kelas" required class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm" @disabled($semuaKelas->isEmpty())>
                        <option value="" selected disabled>Pilih kelas</option>
                        @foreach ($semuaKelas as $namaKelas)<option value="{{ $namaKelas }}">{{ $namaKelas }}</option>@endforeach
                    </select>
                    <button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white" @disabled($semuaKelas->isEmpty())>Tandai semua siswa selesai</button>
                </div>
                <p class="mt-2 text-xs text-slate-500">Pilih kelas, lalu klik tombol untuk mengubah status semua siswa yang masih berlangsung di kelas itu menjadi selesai.</p>
            </form>
            <form action="{{ route('tu.kegiatan.sertifikat.bulk', $kegiatan) }}" method="POST" class="rounded-lg bg-slate-50 p-4">
                @csrf
                <input type="hidden" name="berdasarkan" value="kelas">
                <label for="terbitkan-kelas" class="mb-2 block text-sm font-semibold text-slate-700">Terbitkan sertifikat satu kelas</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select id="terbitkan-kelas" name="nilai" required class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/15 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:opacity-60" @disabled(! $kegiatan->tanggal_selesai->isPast() || $kelasList->isEmpty())>
                        <option value="" selected disabled>Pilih kelas dari Excel</option>
                        @foreach ($kelasList as $namaKelas)
                            <option value="{{ $namaKelas }}">{{ $namaKelas }}</option>
                        @endforeach
                    </select>
                    <button class="shrink-0 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-800 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $kegiatan->tanggal_selesai->isPast() || $kelasList->isEmpty())>
                        Terbitkan kelas
                    </button>
                </div>
                @if (! $kegiatan->tanggal_selesai->isPast())
                    <p class="mt-2 text-xs text-amber-800">Penerbitan tersedia setelah periode PKL berakhir.</p>
                @elseif ($kelasList->isEmpty())
                    <p class="mt-2 text-xs text-slate-500">Belum ada siswa selesai yang dapat menerima sertifikat.</p>
                @endif
            </form>

            <form action="{{ route('tu.kegiatan.sertifikat.zip', $kegiatan) }}" method="GET" class="rounded-lg bg-slate-50 p-4">
                <label for="unduh-kelas" class="mb-2 block text-sm font-semibold text-slate-700">Unduh sertifikat satu kelas</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select id="unduh-kelas" name="kelas" required class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:opacity-60" @disabled($kelasSertifikatList->isEmpty())>
                        <option value="" selected disabled>Pilih kelas yang memiliki sertifikat</option>
                        @foreach ($kelasSertifikatList as $namaKelas)
                            <option value="{{ $namaKelas }}">{{ $namaKelas }}</option>
                        @endforeach
                    </select>
                    <button class="shrink-0 rounded-lg border border-blue-800 px-4 py-2.5 text-sm font-semibold text-blue-900 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-800 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" @disabled($kelasSertifikatList->isEmpty())>
                        Unduh ZIP kelas
                    </button>
                </div>
                @if ($kelasSertifikatList->isEmpty())
                    <p class="mt-2 text-xs text-slate-500">Belum ada sertifikat aktif untuk diunduh.</p>
                @endif
            </form>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">NIS / NISN</th><th class="p-4">Nama</th><th class="p-4">Kelas</th><th class="p-4">Jurusan</th>
                    <th class="p-4">Industri</th><th class="p-4">Status PKL</th><th class="p-4">Sertifikat / Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($peserta as $item)
                    <tr>
                        <td class="p-4">{{ $item->nis }}</td>
                        <td class="p-4 font-semibold">{{ $item->nama }}</td>
                        <td class="p-4">{{ $item->kelas }}</td>
                        <td class="p-4">{{ $item->jurusan ?: '—' }}</td>
                        <td class="p-4">{{ $item->nama_industri }}</td>
                        <td class="p-4"><span class="dashboard-badge" data-status="{{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                        <td class="p-4">
                            @if ($item->sertifikat?->status === 'aktif')
                                <a href="{{ route('tu.sertifikat.download', $item->sertifikat) }}" class="font-semibold text-blue-700">Unduh PDF</a>
                                <span class="mx-1 text-slate-300">·</span>
                                <a href="{{ route('publik.sertifikat.hasil', $item->sertifikat->kode_verifikasi) }}" target="_blank" rel="noopener" class="text-blue-700">Verifikasi</a>
                            @elseif ($item->status === 'selesai' && $kegiatan->tanggal_selesai->isPast())
                                <form action="{{ route('tu.sertifikat.store', [$kegiatan, $item]) }}" method="POST" class="inline">
                                    @csrf
                                    <button class="font-semibold text-blue-700">Terbitkan Sertifikat</button>
                                </form>
                            @else
                                <span class="text-slate-400">Belum siap</span>
                            @endif

                            @if ($item->status !== 'selesai' && $kegiatan->tanggal_selesai->isPast())
                                <form action="{{ route('tu.kegiatan.peserta.complete', [$kegiatan, $item]) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-xs font-semibold text-emerald-700">Tandai PKL selesai</button>
                                </form>
                            @endif

                            @if (! $item->sertifikat)
                                <x-hapus-data :action="route('tu.kegiatan.peserta.destroy', [$kegiatan, $item])"
                                    :label="$item->nama" :message="'Hapus '.$item->nama.' dari periode ini? Data siswa dapat diimpor kembali setelah diperbaiki.'" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-slate-500">Tidak ada siswa yang cocok. Impor daftar siswa atau ubah filter pencarian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $peserta->links() }}</div>
@endsection
