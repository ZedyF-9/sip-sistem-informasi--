@extends('layouts.internal')

@section('title', 'Pratinjau Impor Siswa PKL')

@section('content')
    <div class="mb-6">
        <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="text-sm font-semibold text-blue-700">← Kembali ke periode PKL</a>
        <h2 class="mt-3 text-xl font-bold">Pratinjau Impor</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $preview['total'] }} baris ditemukan · {{ count($preview['rows']) }} siap diimpor · {{ count($preview['errors']) }} bermasalah</p>
    </div>

    @if ($preview['errors'])
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
            <h3 class="font-bold text-red-900">Perbaiki file Excel, lalu unggah ulang</h3>
            <div class="mt-3 max-h-72 space-y-2 overflow-y-auto text-sm text-red-800">
                @foreach ($preview['errors'] as $error)
                    <p>Baris {{ $error['baris'] }} ({{ $error['nis'] ?: 'tanpa NIS' }}, {{ $error['nama'] ?: 'tanpa nama' }}): {{ $error['pesan'] }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50"><tr>
                <th class="p-4">Baris</th><th class="p-4">NIS / NISN</th><th class="p-4">Nama</th><th class="p-4">Kelas</th><th class="p-4">Jurusan</th><th class="p-4">Industri</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($preview['rows'] as $row)
                    <tr>
                        <td class="p-4">{{ $row['baris'] }}</td><td class="p-4">{{ $row['nis'] }}</td>
                        <td class="p-4">{{ $row['nama'] }}</td><td class="p-4">{{ $row['kelas'] }}</td><td class="p-4">{{ $row['jurusan'] ?: '—' }}</td>
                        <td class="p-4">{{ $row['nama_industri'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada baris yang siap diimpor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (! $preview['errors'] && $preview['rows'])
        <form action="{{ route('tu.kegiatan.import.store', $kegiatan) }}" method="POST" class="mt-6">
            @csrf
            <fieldset class="mb-5 rounded-xl border border-slate-200 bg-white p-5">
                <legend class="px-2 font-semibold">Pilih kelas yang akan diimpor</legend>
                <div class="flex flex-wrap gap-3">
                    @foreach (collect($preview['rows'])->pluck('kelas')->unique()->sort() as $kelas)
                        <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                            <input type="checkbox" name="kelas[]" value="{{ $kelas }}" checked>
                            <span>{{ $kelas }} <span class="text-slate-500">({{ collect($preview['rows'])->where('kelas', $kelas)->count() }} siswa)</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="flex gap-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">Impor kelas terpilih ({{ count($preview['rows']) }} siswa)</button>
            <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="rounded-xl border border-slate-300 px-5 py-3">Batal</a>
            </div>
        </form>
    @else
        <a href="{{ route('tu.kegiatan.show', $kegiatan) }}" class="mt-6 inline-block rounded-xl border border-slate-300 px-5 py-3">Kembali dan unggah file yang sudah diperbaiki</a>
    @endif
@endsection
