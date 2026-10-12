@csrf
@php($dataTersimpan = $surat->data_surat ?? [])
@if ($surat->exists)
    <input type="hidden" name="version" value="{{ $surat->version }}">
@endif

<div class="mb-7 max-w-3xl">
    <p class="text-sm leading-6 text-slate-600">Pilih jenis surat, lalu lengkapi data yang diperlukan. Sistem membuat PDF untuk diperiksa berjenjang.</p>
    <p class="mt-2 text-xs text-slate-500"><span class="font-semibold text-red-700">*</span> Wajib diisi. Kolom berubah mengikuti jenis surat.</p>
</div>

<div class="mb-7 rounded-xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
    <h2 class="mb-4 text-base font-bold text-slate-900">Jenis dan identitas surat</h2>
    <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
    <label for="jenis_surat" class="mb-2 block text-sm font-semibold">Jenis surat <span class="text-red-700">*</span></label>
    <select id="jenis_surat" name="jenis_surat" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
        @foreach (['Surat Undangan', 'Surat Perintah Tugas', 'Surat Keputusan', 'Rekomendasi Mutasi Siswa'] as $jenis)
            <option value="{{ $jenis }}" @selected(old('jenis_surat', $surat->jenis_surat ?? 'Surat Undangan') === $jenis)>{{ $jenis }}</option>
        @endforeach
    </select>
    <p class="mt-2 text-xs leading-5 text-slate-500">Kop surat, logo, format isi, dan identitas sekolah ditambahkan otomatis.</p>
</div>

<div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50/60 p-4">
    <p class="text-sm font-semibold text-slate-800">Nomor surat dibuat otomatis saat diterbitkan</p>
    <p class="mt-1 text-xs leading-5 text-slate-600">Nomor berurutan per tahun dan dicantumkan pada PDF serta arsip surat.</p>
</div>
<div>
    <label for="tanggal_surat" class="mb-2 block text-sm font-semibold">Tanggal surat <span class="text-red-700">*</span></label>
    <input id="tanggal_surat" name="tanggal_surat" type="date" value="{{ old('tanggal_surat', isset($surat->tanggal_surat) ? $surat->tanggal_surat->toDateString() : today()->toDateString()) }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
</div>
</div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <section data-type-group="Surat Undangan" class="md:col-span-2 gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="md:col-span-2 mb-1">
            <h2 class="text-base font-bold text-slate-900">Data surat undangan</h2>
            <p class="mt-1 text-xs text-slate-500">Data penerima dan rincian kegiatan yang akan dicantumkan dalam undangan.</p>
        </div>
        @foreach ([
            'penerima_nama' => ['Nama penerima / orang tua / wali', true],
            'nama_siswa' => ['Nama siswa', true],
            'kelas' => ['Kelas siswa', true],
            'alamat_penerima' => ['Alamat penerima', true],
            'nama_kegiatan' => ['Nama kegiatan', true],
            'tahun_pelajaran' => ['Tahun pelajaran', true],
            'keperluan' => ['Keperluan', true],
            'hari_tanggal_kegiatan' => ['Hari dan tanggal kegiatan', true],
            'waktu' => ['Waktu kegiatan', true],
            'tempat_kegiatan' => ['Tempat kegiatan', true],
        ] as $name => [$label, $required])
            <div>
                <label for="undangan_{{ $name }}" class="mb-2 block text-sm font-semibold">{{ $label }} <span class="text-red-700">*</span></label>
                <input id="undangan_{{ $name }}" name="{{ $name }}" value="{{ old($name, $dataTersimpan[$name] ?? null) }}" maxlength="255" data-type-field="Surat Undangan" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
            </div>
        @endforeach
    </section>

    <section data-type-group="Surat Perintah Tugas" class="hidden md:col-span-2 gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="md:col-span-2 mb-1"><h2 class="text-base font-bold text-slate-900">Data surat perintah tugas</h2><p class="mt-1 text-xs text-slate-500">Isi dasar penugasan, identitas petugas, dan rincian kegiatan.</p></div>
        @foreach ([
            'dasar_surat' => 'Dasar surat',
            'nama_penerima_tugas' => 'Nama penerima tugas',
            'pangkat_golongan' => 'Pangkat / golongan',
            'nip' => 'NIPPPK / NIP',
            'jabatan_penerima' => 'Jabatan penerima',
            'tanggal_kegiatan' => 'Tanggal kegiatan',
            'tempat_kegiatan' => 'Tempat kegiatan',
            'tempat_penerbitan' => 'Tempat penerbitan',
        ] as $name => $label)
            <div>
                <label for="tugas_{{ $name }}" class="mb-2 block text-sm font-semibold">{{ $label }} <span class="text-red-700">*</span></label>
                <input id="tugas_{{ $name }}" name="{{ $name }}" value="{{ old($name, $dataTersimpan[$name] ?? null) }}" maxlength="255" data-type-field="Surat Perintah Tugas" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
            </div>
        @endforeach
        <div>
            <label for="uraian_tugas" class="mb-2 block text-sm font-semibold">Uraian tugas / kegiatan <span class="text-red-700">*</span></label>
            <textarea id="uraian_tugas" name="uraian_tugas" rows="3" maxlength="3000" data-type-field="Surat Perintah Tugas" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('uraian_tugas', $dataTersimpan['uraian_tugas'] ?? null) }}</textarea>
        </div>
        <div>
            <label for="daftar_siswa" class="mb-2 block text-sm font-semibold">Daftar nama siswa (satu nama per baris) <span class="text-red-700">*</span></label>
            <textarea id="daftar_siswa" name="daftar_siswa" rows="3" maxlength="10000" placeholder="Satu nama siswa per baris" data-type-field="Surat Perintah Tugas" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('daftar_siswa', $dataTersimpan['daftar_siswa'] ?? null) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="kelas_siswa" class="mb-2 block text-sm font-semibold">Kelas siswa <span class="text-red-700">*</span></label>
            <textarea id="kelas_siswa" name="kelas_siswa" rows="2" maxlength="3000" data-type-field="Surat Perintah Tugas" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('kelas_siswa', $dataTersimpan['kelas_siswa'] ?? null) }}</textarea>
        </div>
    </section>

    <section data-type-group="Surat Keputusan" class="hidden md:col-span-2 gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="md:col-span-2 mb-1"><h2 class="text-base font-bold text-slate-900">Data surat keputusan</h2><p class="mt-1 text-xs text-slate-500">Lengkapi penerima keputusan dan masa berlaku penetapan.</p></div>
        @foreach ([
            'tahun_pelajaran' => 'Tahun pelajaran',
            'nama_tugas' => 'Nama / jenis tugas tambahan',
            'nama_penerima_sk' => 'Nama penerima tugas',
            'jabatan_penerima_sk' => 'Jabatan penerima tugas',
            'tanggal_mulai' => 'Tanggal mulai berlaku',
            'tempat_penerbitan' => 'Tempat penetapan',
        ] as $name => $label)
            <div>
                <label for="sk_{{ $name }}" class="mb-2 block text-sm font-semibold">{{ $label }} <span class="text-red-700">*</span></label>
                <input id="sk_{{ $name }}" name="{{ $name }}" value="{{ old($name, $dataTersimpan[$name] ?? null) }}" maxlength="255" data-type-field="Surat Keputusan" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
            </div>
        @endforeach
        <div>
            <label for="dasar_pertimbangan" class="mb-2 block text-sm font-semibold">Dasar pertimbangan (opsional, jika berbeda)</label>
            <textarea id="dasar_pertimbangan" name="dasar_pertimbangan" rows="3" maxlength="3000" data-type-field="Surat Keputusan" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('dasar_pertimbangan', $dataTersimpan['dasar_pertimbangan'] ?? null) }}</textarea>
        </div>
        <div>
            <label for="dasar_hukum" class="mb-2 block text-sm font-semibold">Peraturan / dasar hukum (opsional, jika berbeda)</label>
            <textarea id="dasar_hukum" name="dasar_hukum" rows="3" maxlength="3000" data-type-field="Surat Keputusan" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('dasar_hukum', $dataTersimpan['dasar_hukum'] ?? null) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="tembusan" class="mb-2 block text-sm font-semibold">Tembusan (opsional, jika berbeda)</label>
            <textarea id="tembusan" name="tembusan" rows="2" maxlength="2000" data-type-field="Surat Keputusan" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('tembusan', $dataTersimpan['tembusan'] ?? null) }}</textarea>
        </div>
    </section>

    <section data-type-group="Rekomendasi Mutasi Siswa" class="hidden md:col-span-2 gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="md:col-span-2 mb-1"><h2 class="text-base font-bold text-slate-900">Data rekomendasi mutasi siswa</h2><p class="mt-1 text-xs text-slate-500">Data siswa, sekolah asal dan tujuan, serta alasan mutasi.</p></div>
        @foreach ([
            'nama_siswa' => ['Nama siswa', true],
            'nisn_nip' => ['NIP / NISN', true],
            'pangkat_golongan' => ['Pangkat / golongan (opsional)', false],
            'jabatan' => ['Jabatan (opsional)', false],
            'kelas_semester' => ['Kelas / semester', true],
            'sekolah_asal' => ['Nama sekolah asal', true],
            'alamat_sekolah_asal' => ['Alamat sekolah asal', true],
            'nama_orang_tua' => ['Nama orang tua / wali', true],
            'alamat_orang_tua' => ['Alamat orang tua / wali', true],
            'sekolah_tujuan' => ['Nama sekolah tujuan', true],
            'tempat_penerbitan' => ['Tempat penerbitan', true],
        ] as $name => [$label, $required])
            <div>
                <label for="mutasi_{{ $name }}" class="mb-2 block text-sm font-semibold">{{ $label }} @if($required)<span class="text-red-700">*</span>@endif</label>
                <input id="mutasi_{{ $name }}" name="{{ $name }}" value="{{ old($name, $dataTersimpan[$name] ?? null) }}" maxlength="255" data-type-field="Rekomendasi Mutasi Siswa" @if($required) data-required="1" @endif class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
            </div>
        @endforeach
        <div>
            <label for="alasan_mutasi" class="mb-2 block text-sm font-semibold">Alasan mutasi / pindah <span class="text-red-700">*</span></label>
            <textarea id="alasan_mutasi" name="alasan_mutasi" rows="3" maxlength="3000" data-type-field="Rekomendasi Mutasi Siswa" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('alasan_mutasi', $dataTersimpan['alasan_mutasi'] ?? null) }}</textarea>
        </div>
        <div>
            <label for="dokumen_lampiran" class="mb-2 block text-sm font-semibold">Dokumen yang dilampirkan <span class="text-red-700">*</span></label>
            <textarea id="dokumen_lampiran" name="dokumen_lampiran" rows="3" maxlength="3000" data-type-field="Rekomendasi Mutasi Siswa" data-required="1" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('dokumen_lampiran', $dataTersimpan['dokumen_lampiran'] ?? null) }}</textarea>
        </div>
    </section>
</div>

<div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
    <a href="{{ route('surat-keluar.index') }}" class="rounded-lg border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700">Batal</a>
    <button class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">{{ $surat->exists ? 'Simpan revisi' : 'Simpan draf surat' }}</button>
</div>

<script>
    (() => {
        const selector = document.getElementById('jenis_surat');
        const groups = document.querySelectorAll('[data-type-group]');
        const updateType = () => {
            groups.forEach((group) => {
                const active = group.dataset.typeGroup === selector.value;
                group.classList.toggle('hidden', !active);
                group.classList.toggle('grid', active);
                group.querySelectorAll('[data-type-field]').forEach((field) => {
                    field.disabled = !active;
                    field.required = active && field.dataset.required === '1';
                });
            });
        };
        selector.addEventListener('change', updateType);
        updateType();
    })();
</script>
