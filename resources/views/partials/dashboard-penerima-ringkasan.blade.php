<section class="role-summary-dashboard" aria-labelledby="school-summary-title">
    <header class="role-summary-header">
        <div>
            <h2 id="school-summary-title">Ringkasan Tugas Anda</h2>
        </div>
        <p>Pantau disposisi yang diterima dan catat perkembangan pekerjaan.</p>
    </header>

    <section class="role-summary-stats" aria-label="Statistik tugas">
        <article class="role-summary-stat role-summary-stat-blue">
            <p>Disposisi Baru</p>
            <strong>{{ number_format($ringkasan['baru'], 0, ',', '.') }}</strong>
            <span>Belum dibuka</span>
        </article>
        <article class="role-summary-stat role-summary-stat-cyan">
            <p>Sedang Diproses</p>
            <strong>{{ number_format($ringkasan['proses'], 0, ',', '.') }}</strong>
            <span>Dalam pengerjaan</span>
        </article>
        <article class="role-summary-stat role-summary-stat-green">
            <p>Sudah Selesai</p>
            <strong>{{ number_format($ringkasan['selesai'], 0, ',', '.') }}</strong>
            <span>Tugas tuntas</span>
        </article>
        <article class="role-summary-stat role-summary-stat-purple">
            <p>Total Disposisi</p>
            <strong>{{ number_format($ringkasan['total'], 0, ',', '.') }}</strong>
            <span>Seluruh riwayat</span>
        </article>
    </section>

    <div class="role-summary-main-grid">
        <section class="role-summary-panel role-summary-mail-panel" aria-labelledby="latest-mail-title">
            <header class="role-summary-panel-header">
                <div>
                    <h3 id="latest-mail-title">Disposisi masuk</h3>
                    <span>Lima instruksi terakhir yang Anda terima.</span>
                </div>
                <a href="{{ route('disposisi.index') }}" class="role-summary-view-all">
                    Lihat semua <x-dashboard-icon name="arrow" />
                </a>
            </header>

            <div class="role-summary-table-wrap" tabindex="0" role="region"
                 aria-label="Disposisi terbaru, geser untuk melihat seluruh kolom">
                <table class="role-summary-table">
                    <thead>
                        <tr>
                            <th>Perihal Surat</th>
                            <th>Pemberi</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($disposisiTerbaru as $disposisi)
                        <tr>
                            <td>
                                <a href="{{ route('disposisi.show', $disposisi) }}">
                                    {{ $disposisi->surat->perihal }}
                                </a>
                            </td>
                            <td>{{ $disposisi->pemberi->name }}</td>
                            <td>{{ $disposisi->batas_waktu ? $disposisi->batas_waktu->format('d/m/Y') : '-' }}</td>
                            <td><span class="dashboard-badge" data-status="{{ $disposisi->status }}">{{ ucfirst($disposisi->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="role-summary-empty">
                                <span class="role-summary-empty-icon"><x-dashboard-icon name="document" /></span>
                                <strong>Belum ada tugas</strong>
                                <span>Disposisi dari Kepala Sekolah akan tampil di sini.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="role-summary-panel role-summary-map" aria-labelledby="operational-map-title">
            <header>
                <h3 id="operational-map-title">Status pekerjaan</h3>
            </header>

            <div class="role-summary-pulse">
                <div class="role-summary-ring" aria-label="{{ $ringkasan['total'] }} total disposisi">
                    <strong>{{ number_format($ringkasan['total'], 0, ',', '.') }}</strong>
                    <span>Total</span>
                </div>
                <div class="role-summary-pulse-copy">
                    <strong>Tindak lanjut</strong>
                    <span>Tuntaskan tugas baru dan perbarui status pengerjaan secara berkala.</span>
                </div>
            </div>

            <div class="role-summary-composition">
                <div class="role-summary-composition-title">
                    <span>Status pengerjaan</span>
                    <small>{{ number_format($ringkasan['total'], 0, ',', '.') }} total</small>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Belum diproses</span><strong>{{ $ringkasan['persen_baru'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-blue" style="width: {{ $ringkasan['persen_baru'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Dalam proses</span><strong>{{ $ringkasan['persen_proses'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-cyan" style="width: {{ $ringkasan['persen_proses'] }}%"></span></div>
                </div>
                <div class="role-summary-progress-group">
                    <div><span>Selesai</span><strong>{{ $ringkasan['persen_selesai'] }}%</strong></div>
                    <div class="role-summary-progress-track"><span class="role-summary-progress-green" style="width: {{ $ringkasan['persen_selesai'] }}%"></span></div>
                </div>
            </div>

            <a href="{{ route('disposisi.index') }}" class="role-summary-report-link">
                <span>Lihat disposisi</span>
                <x-dashboard-icon name="arrow" />
            </a>
        </aside>
    </div>
</section>
