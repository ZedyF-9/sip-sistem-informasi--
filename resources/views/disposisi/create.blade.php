@extends('layouts.internal')

@section('title', 'Buat Disposisi')

@section('content')
    <form method="POST" action="{{ route('disposisi.store') }}"
          class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        <div>
            <label for="surat_masuk_id" class="mb-2 block font-semibold">
                Surat yang sudah diteruskan TU
            </label>

            <select id="surat_masuk_id" name="surat_masuk_id" required
                    class="w-full rounded-xl border border-slate-300 bg-white p-3">
                <option value="">Pilih surat</option>

                @foreach ($surats as $surat)
                    <option value="{{ $surat->id }}"
                        @selected((string) old('surat_masuk_id', request()->query('surat_masuk_id')) === (string) $surat->id)>
                        {{ $surat->nomor_registrasi }} — {{ $surat->perihal }}
                    </option>
                @endforeach
            </select>

            @if ($surats->isEmpty())
                <p class="mt-2 text-sm text-amber-700">
                    Belum ada surat yang dapat diberi disposisi.
                    Pastikan TU sudah memverifikasi dan meneruskan surat.
                </p>
            @endif
        </div>

        <fieldset>
            <legend class="mb-3 font-semibold">Penerima disposisi</legend>

            <p class="mb-3 text-sm text-slate-500">
                Boleh memilih lebih dari satu penerima.
                Instruksi yang sama diberikan kepada semua penerima terpilih.
            </p>

            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($penerimas as $penerima)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
                        <input type="checkbox"
                               name="penerima_ids[]"
                               value="{{ $penerima->id }}"
                               @checked(in_array(
                                   (string) $penerima->id,
                                   array_map('strval', (array) old('penerima_ids', [])),
                                   true
                               ))>

                        <span>{{ $penerima->name }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <label for="instruksi" class="mb-2 block font-semibold">
                Instruksi
            </label>

            <textarea id="instruksi" name="instruksi" rows="5"
                      maxlength="5000" required
                      class="w-full rounded-xl border border-slate-300 p-3">{{ old('instruksi') }}</textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="prioritas" class="mb-2 block font-semibold">
                    Prioritas
                </label>

                <select id="prioritas" name="prioritas" required
                        class="w-full rounded-xl border border-slate-300 bg-white p-3">
                    @foreach ([
                        'biasa' => 'Biasa',
                        'segera' => 'Segera',
                        'sangat_segera' => 'Sangat Segera',
                    ] as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('prioritas', 'biasa') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="batas_waktu" class="mb-2 block font-semibold">
                    Batas waktu (opsional)
                </label>

                <input id="batas_waktu" name="batas_waktu" type="date"
                       value="{{ old('batas_waktu') }}"
                       min="{{ today()->toDateString() }}"
                       class="w-full rounded-xl border border-slate-300 p-3">
            </div>
        </div>

        <div class="flex gap-3">
            <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                Kirim Disposisi
            </button>

            <a href="{{ route('disposisi.index') }}"
               class="rounded-xl border border-slate-300 px-6 py-3">
                Batal
            </a>
        </div>
    </form>
@endsection
