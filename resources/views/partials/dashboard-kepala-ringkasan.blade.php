<section class="role-summary-dashboard" aria-labelledby="school-summary-title">
    <header class="role-summary-header">
        <div>
            <h2 id="school-summary-title">Ringkasan Kerja Kepala Sekolah</h2>
        </div>
        <p>Tinjau disposisi yang diajukan TU sebelum instruksi menjadi aktif.</p>
    </header>

    <section class="role-summary-stats" aria-label="Statistik operasional">
        <article class="role-summary-stat role-summary-stat-blue">
            <p>Persetujuan Disposisi</p>
            <strong>{{ number_format($ringkasan['surat_masuk'], 0, ',', '.') }}</strong>
            <span>Pengajuan dari TU</span>
        </article>
        <article class="role-summary-stat role-summary-stat-amber">
            <p>Persetujuan Surat</p>
            <strong>{{ number_format($ringkasan['surat_keluar'], 0, ',', '.') }}</strong>
            <span>Draf diajukan</span>
        </article>
        <article class="role-summary-stat role-summary-stat-purple">
            <p>Disposisi Aktif</p>
            <strong>{{ number_format($ringkasan['disposisi_aktif'], 0, ',', '.') }}</strong>
            <span>Dalam pemantauan</span>
        </article>
        <article class="role-summary-stat role-summary-stat-green">
            <p>Mitra Industri</p>
            <strong>{{ number_format($ringkasan['mitra_industri'], 0, ',', '.') }}</strong>
            <span>Mitra tercatat</span>
        </article>
    </section>

    <div class="role-summary-main-grid">
        <section class="role-summary-panel role-summary-mail-panel" aria-labelledby="latest-mail-title">
            <header class="role-summary-panel-header">
                <div>
                    <h3 id="latest-mail-title">Antrean Disposisi dari TU</h3>
                    <span>Tinjau pengajuan, lalu isi lembar disposisi resmi setelah menyetujuinya.</span>
                </div>
                <a href="{{ route('disposisi.index') }}" class="role-summary-view-all">
                    Lihat semua <x-dashboard-icon name="arrow" />
                </a>
            </header>

            <div class="role-summary-table-wrap" tabindex="0" role="region"
                 aria-label="Surat perlu disposisi, geser untuk melihat seluruh kolom">
                <table class="role-summary-table">
                    <thead>
                        <tr>
                            <th>Nomor surat</th>
                            <th>Perihal</th>
                            <th>Diajukan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suratTerbaru as $surat)
                        <tr>
                            <td>{{ $surat->surat->nomor_surat ?: $surat->surat->nomor_registrasi }}</td>
                            <td>{{ $surat->surat->perihal }}</td>
                            <td>{{ $surat->tanggal_disposisi->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('disposisi.show', $surat) }}" class="text-blue-700 font-semibold">
                                    Tinjau
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="role-summary-empty">
                                <span class="role-summary-empty-icon"><x-dashboard-icon name="inbox" /></span>
                                <strong>Tidak ada pengajuan menunggu</strong>
                                <span>Disposisi dari TU akan tampil di sini untuk ditinjau.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="role-summary-panel role-summary-map" aria-labelledby="operational-map-title">
            <header>
                <h3 id="operational-map-title">Antrean pekerjaan</h3>
            </header>

            <div class="role-summary-pulse">
                <div class="role-summary-ring" aria-label="{{ $ringkasan['total_tindakan'] }} total tugas">
                    <strong>{{ number_format($ringkasan['total_tindakan'], 0, ',', '.') }}</strong>
                    <span>Tugas</span>
                </div>
                <div class="role-summary-pulse-copy">
                    <strong>Perlu ditindaklanjuti</strong>
                    <span>Total surat dan disposisi yang memerlukan perhatian Anda.</span>
                </div>
            </div>

            <div class="role-summary-composition">
                <div class="role-summary-composition-title">
                    <span>Komposisi antrean</span>
                    <small>{{ number_format($ringkasan['total_tindakan'], 0, ',', '.') }} total</small>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Disposisi</span><strong>{{ $ringkasan['persen_surat'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-blue" style="width: {{ $ringkasan['persen_surat'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Persetujuan surat</span><strong>{{ $ringkasan['persen_keluar'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-amber" style="width: {{ $ringkasan['persen_keluar'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Pemantauan</span><strong>{{ $ringkasan['persen_disposisi'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-purple" style="width: {{ $ringkasan['persen_disposisi'] }}%"></span></div>
                </div>
            </div>

            <a href="{{ route('laporan.index') }}" class="role-summary-report-link">
                <span>Lihat laporan</span>
                <x-dashboard-icon name="arrow" />
            </a>
        </aside>
    </div>

    <section class="role-summary-panel mt-6" aria-labelledby="outgoing-approval-title">
        <header class="role-summary-panel-header">
            <div>
                <h3 id="outgoing-approval-title">Surat Keluar Menunggu Persetujuan</h3>
                <span>Surat yang sudah diperiksa Kepala TU dan diteruskan oleh TU.</span>
            </div>
            <a href="{{ route('surat-keluar.index') }}" class="role-summary-view-all">
                Lihat semua <x-dashboard-icon name="arrow" />
            </a>
        </header>

        <div class="role-summary-table-wrap" tabindex="0" role="region"
             aria-label="Surat keluar menunggu persetujuan Kepala Sekolah">
            <table class="role-summary-table">
                <thead>
                    <tr>
                        <th>Perihal</th>
                        <th>Tujuan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ringkasan['surat_keluar_menunggu'] as $surat)
                        <tr>
                            <td>{{ $surat->perihal }}</td>
                            <td>{{ $surat->tujuan }}</td>
                            <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('surat-keluar.show', $surat) }}" class="font-semibold text-blue-700">
                                    Tinjau surat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="role-summary-empty">
                                <strong>Tidak ada surat keluar menunggu persetujuan.</strong>
                                <span>Surat dari TU yang menunggu keputusan akan tampil di sini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
