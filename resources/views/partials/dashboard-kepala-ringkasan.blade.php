<section class="role-summary-dashboard" aria-labelledby="school-summary-title">
    <header class="role-summary-header">
        <div>
            <p class="role-summary-kicker">Panel Kepemimpinan</p>
            <h2 id="school-summary-title">Ringkasan Kerja Kepala Sekolah</h2>
        </div>
        <p>Tinjauan surat masuk, persetujuan draf, dan pemantauan instruksi.</p>
    </header>

    <section class="role-summary-stats" aria-label="Statistik operasional">
        <article class="role-summary-stat role-summary-stat-blue">
            <p>Surat dari TU</p>
            <strong>{{ number_format($ringkasan['surat_masuk'], 0, ',', '.') }}</strong>
            <span>Menunggu disposisi</span>
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
            <strong aria-label="Data mitra belum tersedia">&mdash;</strong>
            <span>Belum tersedia</span>
        </article>
    </section>

    <div class="role-summary-main-grid">
        <section class="role-summary-panel role-summary-mail-panel" aria-labelledby="latest-mail-title">
            <header class="role-summary-panel-header">
                <div>
                    <p class="role-summary-kicker">Verifikasi TU</p>
                    <h3 id="latest-mail-title">Surat perlu disposisi</h3>
                    <span>Arsip yang telah diperiksa TU dan siap Anda teruskan.</span>
                </div>
                <a href="{{ route('kepala.surat.index') }}" class="role-summary-view-all">
                    Lihat semua <x-dashboard-icon name="arrow" />
                </a>
            </header>

            <div class="role-summary-table-wrap" tabindex="0" role="region"
                 aria-label="Surat perlu disposisi, geser untuk melihat seluruh kolom">
                <table class="role-summary-table">
                    <thead>
                        <tr>
                            <th>Nomor surat</th>
                            <th>Asal</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suratTerbaru as $surat)
                        <tr>
                            <td>{{ $surat->nomor_surat ?: $surat->nomor_registrasi }}</td>
                            <td>{{ $surat->asal_surat }}</td>
                            <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('kepala.surat.show', $surat) }}" class="text-blue-700 font-semibold">
                                    Lihat surat
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="role-summary-empty">
                                <span class="role-summary-empty-icon"><x-dashboard-icon name="inbox" /></span>
                                <strong>Tidak ada antrean surat</strong>
                                <span>Semua surat dari TU telah diproses.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="role-summary-panel role-summary-map" aria-labelledby="operational-map-title">
            <header>
                <p class="role-summary-kicker">Beban kerja</p>
                <h3 id="operational-map-title">Peta persetujuan</h3>
            </header>

            <div class="role-summary-pulse">
                <div class="role-summary-ring" aria-label="{{ $ringkasan['total_tindakan'] }} total tugas">
                    <strong>{{ number_format($ringkasan['total_tindakan'], 0, ',', '.') }}</strong>
                    <span>Tugas</span>
                </div>
                <div class="role-summary-pulse-copy">
                    <strong>Pusat Kendali</strong>
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
                    <div><span>Persetujuan ACC</span><strong>{{ $ringkasan['persen_keluar'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-amber" style="width: {{ $ringkasan['persen_keluar'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Pemantauan</span><strong>{{ $ringkasan['persen_disposisi'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-purple" style="width: {{ $ringkasan['persen_disposisi'] }}%"></span></div>
                </div>
            </div>

            <a href="{{ route('laporan.index') }}" class="role-summary-report-link">
                <span><small>Analisis lebih lanjut</small>Buka pusat laporan</span>
                <x-dashboard-icon name="arrow" />
            </a>
        </aside>
    </div>
</section>
