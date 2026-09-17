@csrf

@if ($mitra->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    @foreach ([
        'nama_mitra' => ['Nama mitra', 'text', 255, true],
        'bidang_usaha' => ['Bidang usaha', 'text', 150, true],
        'telepon' => ['Telepon internal (opsional)', 'text', 30, false],
        'email' => ['Email internal (opsional)', 'email', 255, false],
        'website' => ['Website publik (opsional)', 'url', 255, false],
    ] as $field => $settings)
        <div>
            <label for="{{ $field }}" class="mb-2 block font-semibold">
                {{ $settings[0] }}
            </label>
            <input id="{{ $field }}" name="{{ $field }}"
                   type="{{ $settings[1] }}"
                   maxlength="{{ $settings[2] }}"
                   value="{{ old($field, $mitra->$field) }}"
                   @required($settings[3])
                   class="w-full rounded-xl border border-slate-300 p-3">
        </div>
    @endforeach

    <div>
        <label for="status" class="mb-2 block font-semibold">Status publikasi</label>
        <select id="status" name="status" required
                class="w-full rounded-xl border border-slate-300 bg-white p-3">
            <option value="nonaktif"
                @selected(old('status', $mitra->status ?? 'nonaktif') === 'nonaktif')>
                Nonaktif — tidak tampil di portal publik
            </option>
            <option value="aktif" @selected(old('status', $mitra->status) === 'aktif')>
                Aktif — tampil di portal publik
            </option>
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="alamat" class="mb-2 block font-semibold">Alamat internal (opsional)</label>
        <textarea id="alamat" name="alamat" rows="3" maxlength="2000"
                  class="w-full rounded-xl border border-slate-300 p-3">{{ old('alamat', $mitra->alamat) }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="deskripsi" class="mb-2 block font-semibold">Profil untuk portal publik</label>
        <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="5000" required
                  class="w-full rounded-xl border border-slate-300 p-3">{{ old('deskripsi', $mitra->deskripsi) }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="logo" class="mb-2 block font-semibold">
            Logo (opsional, JPG/PNG/WebP maksimal 2 MB)
        </label>
        <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp"
               class="w-full rounded-xl border border-slate-300 p-3">

        @if ($mitra->exists && $mitra->logo)
            <img src="{{ route('tu.mitra.logo', $mitra) }}"
                 alt="Logo {{ $mitra->nama_mitra }}"
                 class="mt-4 h-20 w-20 rounded-lg border object-contain p-2">
            <p class="mt-2 text-sm text-slate-500">
                Kosongkan unggahan untuk mempertahankan logo.
            </p>
        @endif
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
        Simpan Mitra
    </button>
    <a href="{{ route('tu.mitra.index') }}"
       class="rounded-xl border border-slate-300 px-6 py-3">Batal</a>
</div>