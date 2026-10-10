document.querySelector('.mobile-toggle')?.addEventListener('click', function () {
    const nav = document.querySelector('.main-nav');
    if (!nav) return;
    const open = this.getAttribute('aria-expanded') !== 'true';
    this.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('open', open);
});

document.querySelectorAll('.main-nav a').forEach(a =>
    a.addEventListener('click', () => {
        document.querySelector('.main-nav')?.classList.remove('open');
        document.querySelector('.mobile-toggle')?.setAttribute('aria-expanded', 'false');
    })
);

const topButton = document.querySelector('.back-top');
window.addEventListener(
    'scroll',
    () => topButton?.classList.toggle('visible', window.scrollY > 700),
    { passive: true }
);
topButton?.addEventListener('click', () =>
    window.scrollTo({ top: 0, behavior: 'smooth' })
);

function toast(message) {
    const t = document.getElementById('live-toast');
    if (!t) return;
    t.textContent = message;
    t.hidden = false;
    setTimeout(() => (t.hidden = true), 4500);
}

document.querySelectorAll('form.add-cart').forEach(form =>
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button');
        if (!button) return;
        button.disabled = true;
        form.classList.add('loading');
        try {
            const r = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json' },
            });
            const data = await r.json();
            toast(data.message);
            if (r.ok) {
                document.querySelectorAll('.cart-count').forEach(c => (c.textContent = data.count));
            }
        } catch {
            toast('Unable to update your cart. Please try again.');
        } finally {
            button.disabled = false;
            form.classList.remove('loading');
        }
    })
);

document.querySelectorAll('.category-tabs button[data-category]').forEach(button =>
    button.addEventListener('click', () => {
        document.querySelectorAll('.category-tabs button').forEach(b =>
            b.classList.toggle('selected', b === button)
        );
        const category = button.dataset.category;
        document.querySelectorAll('.featured-item').forEach((item, i) => {
            const show = category === 'all' ? i < 4 : item.dataset.category === category;
            item.style.display = show ? '' : 'none';
        });
    })
);

document.querySelectorAll('[data-confirm]').forEach(form =>
    form.addEventListener('submit', event => {
        if (!confirm(form.dataset.confirm)) event.preventDefault();
    })
);

/* -------- Logout confirmation modal -------- */
(function () {
    const modal = document.getElementById('logout-modal');
    if (!modal) return;

    const open = () => {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        modal.querySelector('button.button')?.focus();
    };
    const close = () => {
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-logout-open]').forEach(btn =>
        btn.addEventListener('click', open)
    );
    document.querySelectorAll('[data-logout-cancel]').forEach(btn =>
        btn.addEventListener('click', close)
    );

    modal.addEventListener('click', e => {
        if (e.target === modal) close();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) close();
    });
})();