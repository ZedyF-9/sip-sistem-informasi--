@props(['for'])

<div
    class="password-strength"
    data-password-strength-for="{{ $for }}"
    data-level="0"
    role="meter"
    aria-label="Kekuatan password"
    aria-valuemin="0"
    aria-valuemax="4"
    aria-valuenow="0"
    aria-valuetext="Belum diisi"
>
    <div class="password-strength-meter" aria-hidden="true">
        <span></span><span></span><span></span><span></span>
    </div>
    <p class="password-strength-copy">
        Kekuatan password: <strong data-password-strength-label>Belum diisi</strong>
    </p>
    <p class="password-strength-hint">
        Gunakan minimal 12 karakter. Kombinasi huruf, angka, dan simbol membantu membuat password lebih kuat.
    </p>
</div>
