<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold">Riwayat Surat Keluar</h2>

    <div class="mt-5 space-y-4">
        @forelse ($riwayats as $item)
            @php
                $snapshot = json_decode($item->snapshot, true) ?? [];
            @endphp

            <article class="rounded-xl border border-slate-200 p-4">
                <p class="font-semibold">
                    {{ ucfirst($item->aksi) }} — {{ $item->petugas }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ \Illuminate\Support\Carbon::parse($item->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                </p>

                <p class="mt-2 text-sm">
                    {{ $item->status_sebelum ?: 'Baru' }}
                    → {{ $item->status_sesudah }}
                </p>

                @if ($item->catatan)
                    <p class="mt-3 whitespace-pre-line text-sm">{{ $item->catatan }}</p>
                @endif

                <details class="mt-3 text-sm">
                    <summary class="cursor-pointer font-semibold text-blue-700">
                        Data pada saat tindakan
                    </summary>
                    <dl class="mt-3 space-y-2">
                        @foreach ($snapshot as $key => $value)
                            @php
                                $displayValue = is_array($value) || is_object($value)
                                    ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                    : $value;
                            @endphp
                            <div>
                                <dt class="text-slate-500">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </dt>
                                <dd class="break-words whitespace-pre-wrap">{{ filled($displayValue) ? $displayValue : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </details>

                <a href="{{ route('surat-keluar.riwayat', [
                    'suratKeluar' => $surat,
                    'riwayat' => $item->id,
                ]) }}"
                   class="mt-3 inline-block text-sm font-semibold text-blue-700">
                    Lihat PDF versi ini
                </a>
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada riwayat.</p>
        @endforelse
    </div>
</section>
