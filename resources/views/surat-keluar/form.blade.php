@csrf

@if ($surat->exists)
    @method('PUT')
    <input type="hidden" name="version" value="{{ $surat->version }}">
@endif

<div class="grid gap-5 md:grid-cols-2">
    @foreach ([
        'perihal' => ['Perihal', 255],
        'tujuan' => ['Tujuan surat', 255],
        'jenis_surat' => ['Jenis surat', 100],
    ] as $field => $settings)
        <div>
            <label for="{{ $field }}" class="mb-2 block font-semibold">
                {{ $settings[0] }}
            </label>
            <input id="{{ $field }}" name="{{ $field }}"
                   value="{{ old($field, $surat->$field) }}"
                   maxlength="{{ $settings[1] }}" required
                   class="w-full rounded-xl border border-slate-300 p-3">
        </div>
    @endforeach

    <div>
        <label for="sifat_surat" class="mb-2 block font-semibold">Sifat surat</label>
        <select id="sifat_surat" name="sifat_surat"
                class="w-full rounded-xl border border-slate-300 bg-white p-3">
            @foreach (['biasa', 'penting', 'rahasia'] as $value)
                <option value="{{ $value }}"
                    @selected(old('sifat_surat', $surat->sifat_surat ?? 'biasa') === $value)>
                    {{ ucfirst($value) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="tanggal_surat" class="mb-2 block font-semibold">Tanggal surat</label>
        <input id="tanggal_surat" name="tanggal_surat" type="date" required
               value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d') ?? today()->toDateString()) }}"
               class="w-full rounded-xl border border-slate-300 p-3">
    </div>

    <div class="md:col-span-2">
        <label for="file_surat" class="mb-2 block font-semibold">
            PDF surat — maksimal 5 MB
        </label>
        <input id="file_surat" name="file_surat" type="file"
               accept=".pdf,application/pdf"
               @required(! $surat->exists)
               class="w-full rounded-xl border border-slate-300 p-3">

        <p class="mt-2 text-sm text-slate-500">
            @if ($surat->exists)
                Kosongkan untuk mempertahankan PDF saat ini.
                PDF lama tetap tersedia dalam riwayat.
            @else
                Siapkan dokumen surat sebelum mengajukan persetujuan.
            @endif
        </p>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
        Simpan Draf
    </button>
    <a href="{{ route('surat-keluar.index') }}"
       class="rounded-xl border border-slate-300 px-6 py-3">Batal</a>
</div>