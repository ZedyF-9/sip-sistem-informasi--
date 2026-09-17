// Presentation only: the existing links and forms retain their normal behavior.
const sidebar = document.getElementById('dashboard-sidebar');
const toggle = document.querySelector('[data-navigation-toggle]');

if (sidebar && toggle) {
    const mobile = window.matchMedia('(max-width: 1023px)');
    const backdrop = document.querySelector('[data-navigation-backdrop]');
    let previousFocus;

    const setOpen = (open, restoreFocus = true) => {
        const visible = open && mobile.matches;
        document.body.classList.toggle('dashboard-navigation-open', visible);
        toggle.setAttribute('aria-expanded', String(visible));
        sidebar.inert = mobile.matches && !visible;
        if (backdrop) backdrop.hidden = !visible;
        if (visible) {
            previousFocus = document.activeElement;
            sidebar.querySelector('[data-navigation-close]')?.focus();
        } else if (restoreFocus && previousFocus) {
            previousFocus.focus();
            previousFocus = null;
        }
    };

    document.body.dataset.navigationReady = 'true';
    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    document.querySelector('[data-navigation-close]')?.addEventListener('click', () => setOpen(false));
    backdrop?.addEventListener('click', () => setOpen(false));
    mobile.addEventListener('change', () => setOpen(false, false));
    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('dashboard-navigation-open')) return;
        if (event.key === 'Escape') setOpen(false);
        if (event.key === 'Tab') {
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled])')]
                .filter((element) => element.getClientRects().length);
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });
    setOpen(false, false);
}
