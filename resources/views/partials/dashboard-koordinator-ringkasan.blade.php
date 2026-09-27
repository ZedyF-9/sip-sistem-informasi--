<section class="role-summary-dashboard" aria-labelledby="school-summary-title">
    <header class="role-summary-header">
        <div>
            <h2 id="school-summary-title">Ringkasan Data Sekolah</h2>
        </div>
        <p>Jumlah surat dan pengajuan yang tercatat di sekolah.</p>
    </header>

    <section class="role-summary-stats" aria-label="Statistik persuratan">
        <article class="role-summary-stat role-summary-stat-blue">
            <p>Surat Masuk</p>
            <strong>{{ number_format($ringkasan['surat_masuk'], 0, ',', '.') }}</strong>
            <span>Arsip diterima</span>
        </article>
        <article class="role-summary-stat role-summary-stat-cyan">
            <p>Surat Keluar</p>
            <strong>{{ number_format($ringkasan['surat_keluar'], 0, ',', '.') }}</strong>
            <span>Surat tercatat</span>
        </article>
        <article class="role-summary-stat role-summary-stat-amber">
            <p>Menunggu persetujuan</p>
            <strong>{{ number_format($ringkasan['menunggu_acc'], 0, ',', '.') }}</strong>
            <span>Surat keluar diajukan</span>
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
                    <h3 id="latest-mail-title">Surat masuk terbaru</h3>
                    <span>Lima arsip terakhir yang tercatat di sistem.</span>
                </div>
                <a href="{{ route('tu.surat-masuk.index') }}" class="role-summary-view-all">
                    Lihat semua <x-dashboard-icon name="arrow" />
                </a>
            </header>

            <div class="role-summary-table-wrap" tabindex="0" role="region"
                 aria-label="Surat masuk terbaru, geser untuk melihat seluruh kolom">
                <table class="role-summary-table">
                    <thead>
                        <tr>
                            <th>Nomor surat</th>
                            <th>Asal</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suratTerbaru as $surat)
                        <tr>
                            <td>
                                <a href="{{ route('tu.surat-masuk.show', $surat) }}">
                                    {{ $surat->nomor_surat ?: $surat->nomor_registrasi }}
                                </a>
                            </td>
                            <td>{{ $surat->asal_surat }}</td>
                            <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
                            <td><span class="dashboard-badge" data-status="{{ $surat->status }}">{{ $surat->statusLabel() }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="role-summary-empty">
                                <span class="role-summary-empty-icon"><x-dashboard-icon name="inbox" /></span>
                                <strong>Belum ada surat masuk</strong>
                                <span>Surat yang dicatat akan tampil di sini.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="role-summary-panel role-summary-map" aria-labelledby="operational-map-title">
            <header>
                <h3 id="operational-map-title">Ringkasan persetujuan</h3>
            </header>

            <div class="role-summary-pulse">
                <div class="role-summary-ring" aria-label="{{ $ringkasan['menunggu_acc'] }} antrean ACC">
                    <strong>{{ number_format($ringkasan['menunggu_acc'], 0, ',', '.') }}</strong>
                    <span>Menunggu</span>
                </div>
                <div class="role-summary-pulse-copy">
                    <strong>{{ $ringkasan['persen_acc'] }}%</strong>
                    <span>dari seluruh surat sedang menunggu persetujuan.</span>
                </div>
            </div>

            <div class="role-summary-composition">
                <div class="role-summary-composition-title">
                    <span>Komposisi dokumen</span>
                    <small>{{ number_format($ringkasan['total_surat'], 0, ',', '.') }} total</small>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Surat masuk</span><strong>{{ $ringkasan['persen_masuk'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-blue" style="width: {{ $ringkasan['persen_masuk'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Surat keluar</span><strong>{{ $ringkasan['persen_keluar'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-cyan" style="width: {{ $ringkasan['persen_keluar'] }}%"></span></div>
                </div>
            </div>

            <a href="{{ route('laporan.index') }}" class="role-summary-report-link">
                <span>Lihat laporan</span>
                <x-dashboard-icon name="arrow" />
            </a>
        </aside>
    </div>
</section>
