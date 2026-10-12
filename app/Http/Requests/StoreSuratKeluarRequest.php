<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuratKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'koordinator_tu'
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        $jenis = [
            'Surat Undangan',
            'Surat Perintah Tugas',
            'Surat Keputusan',
            'Rekomendasi Mutasi Siswa',
        ];
        $required = [
            'penerima_nama' => ['Surat Undangan'],
            'nama_siswa' => ['Surat Undangan', 'Rekomendasi Mutasi Siswa'],
            'kelas' => ['Surat Undangan'],
            'alamat_penerima' => ['Surat Undangan'],
            'nama_kegiatan' => ['Surat Undangan'],
            'tahun_pelajaran' => ['Surat Undangan', 'Surat Keputusan'],
            'keperluan' => ['Surat Undangan'],
            'hari_tanggal_kegiatan' => ['Surat Undangan'],
            'waktu' => ['Surat Undangan'],
            'tempat_kegiatan' => ['Surat Undangan', 'Surat Perintah Tugas'],
            'dasar_surat' => ['Surat Perintah Tugas'],
            'nama_penerima_tugas' => ['Surat Perintah Tugas'],
            'pangkat_golongan' => ['Surat Perintah Tugas'],
            'nip' => ['Surat Perintah Tugas'],
            'jabatan_penerima' => ['Surat Perintah Tugas'],
            'uraian_tugas' => ['Surat Perintah Tugas'],
            'tanggal_kegiatan' => ['Surat Perintah Tugas'],
            'daftar_siswa' => ['Surat Perintah Tugas'],
            'kelas_siswa' => ['Surat Perintah Tugas'],
            'tempat_penerbitan' => ['Surat Perintah Tugas', 'Surat Keputusan', 'Rekomendasi Mutasi Siswa'],
            'nama_tugas' => ['Surat Keputusan'],
            'nama_penerima_sk' => ['Surat Keputusan'],
            'jabatan_penerima_sk' => ['Surat Keputusan'],
            'tanggal_mulai' => ['Surat Keputusan'],
            'nisn_nip' => ['Rekomendasi Mutasi Siswa'],
            'kelas_semester' => ['Rekomendasi Mutasi Siswa'],
            'sekolah_asal' => ['Rekomendasi Mutasi Siswa'],
            'alamat_sekolah_asal' => ['Rekomendasi Mutasi Siswa'],
            'nama_orang_tua' => ['Rekomendasi Mutasi Siswa'],
            'alamat_orang_tua' => ['Rekomendasi Mutasi Siswa'],
            'sekolah_tujuan' => ['Rekomendasi Mutasi Siswa'],
            'alasan_mutasi' => ['Rekomendasi Mutasi Siswa'],
            'dokumen_lampiran' => ['Rekomendasi Mutasi Siswa'],
        ];
        $rules = [
            'version' => ['sometimes', 'integer', 'min:1'],
            'jenis_surat' => ['required', Rule::in($jenis)],
            'tanggal_surat' => ['required', 'date'],
            'tahun_pelajaran' => ['nullable', 'string', 'max:30'],
            'tempat_penerbitan' => ['nullable', 'string', 'max:150'],
            'dasar_pertimbangan' => ['nullable', 'string', 'max:3000'],
            'dasar_hukum' => ['nullable', 'string', 'max:3000'],
            'tembusan' => ['nullable', 'string', 'max:2000'],
            'pangkat_golongan' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ];

        foreach ($required as $field => $jenisWajib) {
            $rules[$field] = ['required_if:jenis_surat,'.implode(',', $jenisWajib), 'nullable', 'string', 'max:10000'];
        }

        foreach (array_keys($required) as $field) {
            $rules[$field] ??= ['nullable', 'string', 'max:10000'];
        }
        $rules['tanggal_surat'] = ['required', 'date'];
        $rules['tanggal_kegiatan'] = ['nullable', 'string', 'max:255'];
        $rules['hari_tanggal_kegiatan'] = ['nullable', 'string', 'max:255'];
        $rules['waktu'] = ['nullable', 'string', 'max:255'];
        $rules['tanggal_mulai'] = ['nullable', 'date'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk jenis surat ini.',
        ];
    }
}
