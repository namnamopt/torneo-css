/* Centro de Canje · interacciones generales */
(() => {
    'use strict';
    document.documentElement.classList.add('js');

    // Menú móvil
    const burger = document.getElementById('burger');
    const nav = document.getElementById('nav');
    if (burger && nav) {
        burger.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', String(open));
            burger.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        });
    }

    // Cuenta regresiva al partido
    document.querySelectorAll('[data-countdown]').forEach((box) => {
        const target = new Date(box.dataset.countdown).getTime();
        if (Number.isNaN(target)) return;
        const cells = {
            d: box.querySelector('[data-u="d"]'), h: box.querySelector('[data-u="h"]'),
            m: box.querySelector('[data-u="m"]'), s: box.querySelector('[data-u="s"]'),
        };
        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            let diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
            if (target - Date.now() <= 0) {
                box.innerHTML = '<p class="countdown-done">Partido jugado · próxima fecha pronto</p>';
                return false;
            }
            const d = Math.floor(diff / 86400); diff -= d * 86400;
            const h = Math.floor(diff / 3600);  diff -= h * 3600;
            const m = Math.floor(diff / 60);
            cells.d.textContent = pad(d); cells.h.textContent = pad(h);
            cells.m.textContent = pad(m); cells.s.textContent = pad(diff - m * 60);
            return true;
        };
        if (tick()) {
            const id = setInterval(() => { if (!tick()) clearInterval(id); }, 1000);
        }
    });

    // Aparición suave al hacer scroll
    const items = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && items.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
        }, { threshold: 0.08 });
        items.forEach((el) => io.observe(el));
    } else {
        items.forEach((el) => el.classList.add('in'));
    }

    // Botón imprimir
    document.querySelectorAll('[data-print]').forEach((b) => b.addEventListener('click', () => window.print()));
})();
