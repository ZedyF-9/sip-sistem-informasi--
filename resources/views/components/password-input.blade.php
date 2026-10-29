@props(['invalid' => false])

<div class="password-input">
    <input
        {{ $attributes->merge(['type' => 'password']) }}
        @if ($invalid)
            aria-invalid="true"
            aria-describedby="{{ $attributes->get('id') }}-error"
        @endif
    >
    <button
        type="button"
        class="password-input-toggle"
        data-password-toggle
        aria-controls="{{ $attributes->get('id') }}"
        aria-label="Tampilkan password"
        aria-pressed="false"
    >
        <svg data-password-icon="visible" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M2.25 12s3.5-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" />
            <circle cx="12" cy="12" r="2.5" />
        </svg>
        <svg data-password-icon="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M3.5 13.5c2.1 2.1 5 3.15 8.5 3.15s6.4-1.05 8.5-3.15" />
            <path d="m5.6 8.4-1.4-1.4M12 6.9V5m6.4 3.4 1.4-1.4" />
        </svg>
    </button>
</div>
