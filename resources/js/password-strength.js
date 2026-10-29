const labelByLevel = {
    1: 'Lemah',
    2: 'Cukup',
    3: 'Kuat',
    4: 'Sangat kuat',
};

function assessPassword(value) {
    const length = Array.from(value).length;

    if (length === 0) {
        return { level: 0, label: 'Belum diisi' };
    }

    if (length < 12) {
        return { level: 1, label: 'Perlu minimal 12 karakter' };
    }

    const variety = [
        /\p{Ll}/u.test(value),
        /\p{Lu}/u.test(value),
        /\p{N}/u.test(value),
        /[^\p{L}\p{N}\s]/u.test(value),
    ].filter(Boolean).length;

    let level = 1;

    if (length >= 16 || variety >= 2) level += 1;
    if ((length >= 20 && variety >= 2) || variety >= 3) level += 1;
    if (length >= 24 || variety === 4) level += 1;

    level = Math.min(level, 4);

    return { level, label: labelByLevel[level] };
}

document.querySelectorAll('[data-password-strength-for]').forEach((meter) => {
    const input = document.getElementById(meter.dataset.passwordStrengthFor);
    const label = meter.querySelector('[data-password-strength-label]');

    if (!(input instanceof HTMLInputElement) || !label) {
        return;
    }

    input.addEventListener('input', () => {
        const { level, label: strengthLabel } = assessPassword(input.value);

        meter.dataset.level = String(level);
        meter.setAttribute('aria-valuenow', String(level));
        meter.setAttribute('aria-valuetext', strengthLabel);
        label.textContent = strengthLabel;
    });
});
