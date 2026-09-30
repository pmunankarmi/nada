/** Accessible menu interactions. Content and translations are rendered by WordPress. */
(function () {
    'use strict';
    const nav = document.getElementById('nav');
    const updateNav = () => nav?.classList.toggle('is-solid', window.scrollY > 24);
    window.addEventListener('scroll', updateNav, { passive: true });
    updateNav();
    const toggle = document.getElementById('burger');
    const drawer = document.getElementById('drawer');
    if (!toggle || !drawer) return;

    function setOpen(open) {
        toggle.setAttribute('aria-expanded', String(open));
        drawer.setAttribute('aria-hidden', String(!open));
        drawer.inert = !open;
        drawer.classList.toggle('is-open', open);
        document.body.classList.toggle('nada-menu-open', open);
        if (open) drawer.querySelector('a')?.focus();
        else toggle.focus();
    }
    toggle.addEventListener('click', function () {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('keydown', function (event) {
        if (toggle.getAttribute('aria-expanded') !== 'true') return;
        if (event.key === 'Escape') setOpen(false);
        if (event.key === 'Tab') {
            const links = Array.from(drawer.querySelectorAll('a, button'));
            const first = links[0];
            const last = links[links.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first?.focus();
            }
        }
    });
})();
