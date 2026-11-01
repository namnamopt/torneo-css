/* Centro de Canje · formulario de reserva: resumen en vivo, mapa del estadio y cantidad */
(() => {
    'use strict';
    const cfgEl = document.getElementById('cfg');
    const form = document.getElementById('form-reserva');
    if (!cfgEl || !form) return;

    const cfg = JSON.parse(cfgEl.textContent);
    const $ = (s, r = document) => r.querySelector(s);
    const radios = [...form.querySelectorAll('input[name="sector"]')];
    const cond = [...form.querySelectorAll('input[name="condicion"]')];
    const qty = $('#cantidad');
    const stands = [...document.querySelectorAll('.stand[data-sector]')];
    const out = {
        sector: $('[data-sum="sector"]'), cond: $('[data-sum="cond"]'), unit: $('[data-sum="unit"]'),
        cant: $('[data-sum="cant"]'), total: $('[data-sum="total"]'), disp: $('[data-sum="disp"]'),
    };
    const gs = (n) => new Intl.NumberFormat('es-PY').format(n) + ' Gs.';
    const checked = (list) => list.find((r) => r.checked);

    function selectSector(key) {
        const r = radios.find((x) => x.value === key);
        if (r && !r.disabled) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); }
    }

    function render() {
        const sKey = (checked(radios) || {}).value;
        const cKey = (checked(cond) || {}).value;
        const s = sKey ? cfg.sectores[sKey] : null;
        const n = Math.max(1, Math.min(cfg.max, parseInt(qty.value, 10) || 1));
        const unit = s && cKey ? (cKey === 'socio' ? s.socio : s.general) : 0;

        stands.forEach((g) => {
            const on = g.dataset.sector === sKey;
            g.classList.toggle('is-sel', on);
            g.setAttribute('aria-pressed', String(on));
        });

        out.sector.textContent = s ? s.nombre : '—';
        out.cond.textContent = cKey ? (cKey === 'socio' ? 'Socio' : 'No socio') : '—';
        out.unit.textContent = unit ? gs(unit) : '—';
        out.cant.textContent = String(n);
        out.total.textContent = unit ? gs(unit * n) : '—';
        out.disp.textContent = s ? new Intl.NumberFormat('es-PY').format(s.disp) + ' disponibles' : '—';

        const limit = s ? Math.min(cfg.max, s.disp) : cfg.max;
        qty.max = String(limit);
        if (n > limit && limit > 0) qty.value = String(limit);
    }

    radios.forEach((r) => r.addEventListener('change', render));
    cond.forEach((r) => r.addEventListener('change', render));
    qty.addEventListener('input', render);
    qty.addEventListener('change', () => { qty.value = String(Math.max(1, Math.min(parseInt(qty.max, 10) || cfg.max, parseInt(qty.value, 10) || 1))); render(); });

    form.querySelectorAll('[data-step]').forEach((b) => b.addEventListener('click', () => {
        const v = (parseInt(qty.value, 10) || 1) + parseInt(b.dataset.step, 10);
        qty.value = String(Math.max(1, Math.min(parseInt(qty.max, 10) || cfg.max, v)));
        render();
    }));

    stands.forEach((g) => {
        g.addEventListener('click', () => selectSector(g.dataset.sector));
        g.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectSector(g.dataset.sector); } });
    });

    form.addEventListener('reset', () => setTimeout(render, 0));
    render();
})();
