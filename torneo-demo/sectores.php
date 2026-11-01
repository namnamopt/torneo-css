<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$S = sectores();
$disp = disponibles();
$pageTitle = 'Sectores y precios';
$pageDesc  = 'Sectores del estadio, capacidad, disponibilidad y precios general y de socio.';
$active    = 'sectores';

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p class="eyebrow">// SECTORES DEL ESTADIO</p>
        <h1 class="h-xl">Sectores y precios</h1>
        <p class="lead">Cinco sectores, dos tarifas. Los precios de socio se aplican al indicar tu condición en la reserva. Importes en guaraníes por entrada.</p>
    </div>
</section>

<section class="section">
    <div class="wrap two-col two-col--wide">
        <div>
            <table class="tbl" id="tabla-sectores">
                <thead>
                    <tr>
                        <th>Sector</th>
                        <th class="num">Capacidad</th>
                        <th class="num">Precio general</th>
                        <th class="num">Precio socio</th>
                        <th>Disponibilidad</th>
                        <th><span class="sr">Acción</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($S as $k => $s):
                    $d = $disp[$k];
                    $pct = $s['cap'] > 0 ? round($d / $s['cap'] * 100) : 0;
                ?>
                    <tr data-sector="<?= e($k) ?>" style="--c:<?= e($s['color']) ?>">
                        <td class="sc-first">
                            <div class="sname"><div><b><?= e($s['nombre']) ?></b><small><?= e($s['desc']) ?></small></div></div>
                        </td>
                        <td class="num" data-l="Capacidad"><?= e(fmt_num($s['cap'])) ?></td>
                        <td class="num" data-l="General"><?= e(fmt_num($s['general'])) ?></td>
                        <td class="num price-socio" data-l="Socio"><?= e(fmt_num($s['socio'])) ?></td>
                        <td data-l="Disponible">
                            <?php if ($d <= 0): ?>
                                <span class="tag tag--warn">Agotado</span>
                            <?php else: ?>
                                <span class="num" style="text-align:left"><?= e(fmt_num($d)) ?></span>
                                <span class="meter" aria-hidden="true"><i style="width:<?= (int) $pct ?>%"></i></span>
                            <?php endif; ?>
                        </td>
                        <td data-l="">
                            <?php if ($d > 0): ?>
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('reservar.php?sector=' . $k)) ?>">Reservar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="note">Disponibilidad calculada con las reservas registradas en este sistema. Los extras (estacionamiento, camiseta, programa) se abonan al retirar.</p>
        </div>

        <div class="map-card">
            <?= stadium_svg($disp) ?>
            <p class="note">Esquema ilustrativo · pasa el cursor por un sector</p>
        </div>
    </div>
</section>

<section class="section cta">
    <div class="wrap cta__in">
        <div>
            <p class="eyebrow">// ¿YA ELEGISTE?</p>
            <h2 class="h-xl">Reserva y canjea en el centro</h2>
        </div>
        <a class="btn" href="<?= e(url('reservar.php')) ?>">Reservar entradas</a>
    </div>
</section>

<?php
$extraScripts = <<<'HTML'
<script>
(() => {
    const rows = [...document.querySelectorAll('#tabla-sectores tbody tr')];
    const stands = [...document.querySelectorAll('.stand[data-sector]')];
    const hl = (k, on) => rows.forEach((r) => r.classList.toggle('is-hl', on && r.dataset.sector === k));
    stands.forEach((g) => {
        g.addEventListener('mouseenter', () => hl(g.dataset.sector, true));
        g.addEventListener('mouseleave', () => hl(g.dataset.sector, false));
        g.addEventListener('focus', () => hl(g.dataset.sector, true));
        g.addEventListener('blur', () => hl(g.dataset.sector, false));
        g.addEventListener('click', () => { location.href = 'reservar.php?sector=' + g.dataset.sector; });
        g.addEventListener('keydown', (e) => { if (e.key === 'Enter') location.href = 'reservar.php?sector=' + g.dataset.sector; });
    });
    rows.forEach((r) => {
        r.addEventListener('mouseenter', () => stands.forEach((g) => g.classList.toggle('is-sel', g.dataset.sector === r.dataset.sector)));
        r.addEventListener('mouseleave', () => stands.forEach((g) => g.classList.remove('is-sel')));
    });
})();
</script>
HTML;
require __DIR__ . '/includes/footer.php';
?>
