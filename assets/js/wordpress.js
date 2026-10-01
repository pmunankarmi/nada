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

    function setOpen(open, restoreFocus = true) {
        toggle.setAttribute('aria-expanded', String(open));
        drawer.setAttribute('aria-hidden', String(!open));
        drawer.inert = !open;
        drawer.classList.toggle('is-open', open);
        document.body.classList.toggle('nada-menu-open', open);
        if (open) drawer.querySelector('a')?.focus();
        else if (restoreFocus) toggle.focus();
    }
    // Keep state in sync with the stylesheet's mobile navigation breakpoint.
    const mobileMenu = window.matchMedia('(max-width: 1080px)');
    mobileMenu.addEventListener('change', event => {
        if (event.matches || toggle.getAttribute('aria-expanded') !== 'true') return;
        const hadMenuFocus = drawer.contains(document.activeElement) || document.activeElement === toggle;
        setOpen(false, false);
        if (hadMenuFocus) nav.querySelector('.nav__links a')?.focus();
    });
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

/** Search and filter the complete WordPress catalog without changing pages. */
(function () {
    'use strict';
    document.querySelectorAll('[data-catalog]').forEach(catalog => {
        const grid = catalog.querySelector('[data-catalog-grid]');
        const cards = Array.from(grid.querySelectorAll(':scope > article'));
        const search = document.querySelector('[data-recipe-search]');
        const count = document.querySelector('[data-recipe-count]');
        const empty = catalog.querySelector('[data-catalog-empty]');
        let category = 'all';
        const update = () => {
            const query = (search?.value || '').trim().toLocaleLowerCase();
            let visible = 0;
            cards.forEach(card => {
                const matchesCategory = category === 'all' || card.dataset.categories.split(' ').includes(category);
                const matchesSearch = !query || card.textContent.toLocaleLowerCase().includes(query);
                card.hidden = !(matchesCategory && matchesSearch);
                if (!card.hidden) visible++;
            });
            if (count) count.textContent = `${visible.toLocaleString(document.documentElement.lang)} ${count.dataset.label}`;
            empty.hidden = visible > 0;
        };
        if (grid.dataset.filterInline === 'true') {
            catalog.querySelectorAll('[data-filter]').forEach(filter => {
                filter.addEventListener('click', event => {
                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                    event.preventDefault();
                    category = filter.dataset.filter;
                    catalog.querySelectorAll('[data-filter]').forEach(item => {
                        item.classList.toggle('is-active', item === filter);
                        if (item === filter) item.setAttribute('aria-current', 'true');
                        else item.removeAttribute('aria-current');
                    });
                    update();
                });
            });
        }
        search?.addEventListener('input', update);
        update();
    });

    // Native modal dialogs provide focus trapping, Escape and an inert background.
    document.querySelectorAll('[data-recipe-open]').forEach(opener => {
        const dialog = document.getElementById(opener.dataset.recipeOpen);
        if (!dialog) return;
        opener.addEventListener('click', () => {
            dialog.showModal();
            dialog.classList.add('is-open');
            document.body.classList.add('nada-recipe-open');
        });
        dialog.querySelectorAll('[data-recipe-close]').forEach(close => {
            close.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('close', () => {
            dialog.classList.remove('is-open');
            document.body.classList.remove('nada-recipe-open');
            opener.focus();
        });
    });
})();
