<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
start_session();

$P  = partido();
$ts = partido_ts();
$S  = sectores();
$X  = extras();

$pageTitle = 'Mi reserva';
$pageDesc  = 'Consulta tu reserva con tu código y tu cédula.';
$active    = 'mireserva';

$errores = [];
$reserva = null;
$esNueva = false;
$codigoIn = strtoupper(clean_text($_GET['c'] ?? '', 12));

// 1) Recién creada en esta sesión: se muestra sin pedir cédula.
if ($codigoIn !== '' && ($_SESSION['ultima_reserva'] ?? '') === $codigoIn) {
    $reserva = reserva_buscar($codigoIn);
    $esNueva = $reserva !== null && !empty($_GET['nueva']);
}

// 2) Consulta con código + cédula.
if ($reserva === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoIn = strtoupper(clean_text($_POST['codigo'] ?? '', 12));
    $ced = clean_cedula(clean_text($_POST['cedula'] ?? '', 20));
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    } elseif (!throttle('consulta', 3)) {
        $errores[] = 'Espera unos segundos antes de volver a consultar.';
    } else {
        $r = reserva_buscar($codigoIn);
        if ($r !== null && $ced !== '' && hash_equals((string) $r['cedula'], $ced)) {
            $reserva = $r;
        } else {
            $errores[] = 'No encontramos una reserva con ese código y esa cédula.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p class="eyebrow">// CANJE</p>
        <h1 class="h-xl"><?= $reserva ? 'Tu reserva' : 'Consultar mi reserva' ?></h1>
        <p class="lead"><?= $reserva
            ? 'Presenta este código y tu cédula en el centro de canje para retirar tus entradas.'
            : 'Ingresa tu código de reserva y tu número de cédula.' ?></p>
    </div>
</section>

<section class="section">
<div class="wrap">
<?php if ($reserva):
    $s = $S[$reserva['sector']] ?? ['nombre' => $reserva['sector'], 'color' => '#ffc531'];
    $retiro = strtotime($reserva['fecha_retiro'] . ' 12:00');
    $ex = array_map(fn($k) => $X[$k] ?? $k, (array) ($reserva['extras'] ?? []));
?>
    <?php if ($esNueva): ?>
        <div class="alert alert--ok" role="status" style="max-width:860px;margin-inline:auto;margin-bottom:1.6rem">
            <strong>¡Reserva registrada!</strong> Guarda tu código <b><?= e($reserva['codigo']) ?></b>. Lo necesitarás junto con tu cédula para canjear.
        </div>
    <?php endif; ?>

    <div class="ticket-wrap">
        <article class="ticket ticket--res" aria-label="Reserva <?= e($reserva['codigo']) ?>" style="--c:<?= e($s['color']) ?>">
            <div class="ticket__main">
                <span class="ticket__sector"><?= e($s['nombre']) ?> · <?= (int) $reserva['cantidad'] ?> <?= (int) $reserva['cantidad'] === 1 ? 'entrada' : 'entradas' ?></span>
                <p class="ticket__teams"><?= e($P['local']) ?><span>vs</span><?= e($P['visita']) ?></p>
                <dl class="ticket__list">
                    <div><dt>Titular</dt><dd><?= e($reserva['nombre'] . ' ' . $reserva['apellido']) ?></dd></div>
                    <div><dt>Cédula</dt><dd><?= e($reserva['cedula']) ?></dd></div>
                    <div><dt>Partido</dt><dd><?= e(fecha_es($ts)) ?> · <?= e(date('H:i', $ts)) ?> hs</dd></div>
                    <div><dt>Retiro en el centro</dt><dd><?= e(fecha_es($retiro)) ?></dd></div>
                    <div><dt>Condición</dt><dd><?= $reserva['condicion'] === 'socio' ? 'Socio' : 'No socio' ?> · <?= e(fmt_gs((int) $reserva['precio_unit'])) ?> c/u</dd></div>
                    <div><dt>Extras</dt><dd><?= $ex ? e(implode(', ', $ex)) : 'Ninguno' ?></dd></div>
                </dl>
                <p class="ticket__serial" style="margin:0">TOTAL A ABONAR AL RETIRAR</p>
                <p class="ticket__total"><?= e(fmt_gs((int) $reserva['total'])) ?></p>
            </div>
            <div class="ticket__stub">
                <span class="ticket__serial">Reserva</span>
                <?= barcode_svg($reserva['codigo'], true) ?>
                <?= barcode_svg($reserva['codigo'], false) ?>
                <p class="ticket__code"><?= e($reserva['codigo']) ?></p>
            </div>
        </article>
    </div>

    <div class="actions">
        <button class="btn" type="button" data-print>Imprimir reserva</button>
        <a class="btn btn--ghost" href="<?= e(url('como-llegar.php')) ?>">Cómo llegar</a>
        <a class="btn btn--ghost" href="<?= e(url('reservar.php')) ?>">Nueva reserva</a>
    </div>

    <div class="narrow noprint" style="margin:3rem auto 0">
        <p class="eyebrow">// QUÉ HACER AHORA</p>
        <ol class="todo">
            <li>Ven al centro de canje el <b><?= e(fecha_es($retiro)) ?></b>. Horario: <?= e($P['boleteria']['horario']) ?>.</li>
            <li>Lleva tu <b>cédula</b> y tu código <b><?= e($reserva['codigo']) ?></b> (impreso o en el celular).</li>
            <li>Abona <b><?= e(fmt_gs((int) $reserva['total'])) ?></b> y recibe tus entradas.</li>
            <li>El día del partido, valida el ticket en el torniquete de tu sector.</li>
        </ol>
        <p class="note">Boletería: <?= e($P['boleteria']['direccion']) ?> · <a href="tel:<?= e($P['boleteria']['tel_href']) ?>"><?= e($P['boleteria']['telefono']) ?></a></p>
    </div>

<?php else: ?>
    <div class="narrow" style="margin-inline:auto">
        <?php if ($errores): ?>
            <div class="alert alert--err" role="alert"><ul><?php foreach ($errores as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form class="form form-block" method="post" action="<?= e(url('mi-reserva.php')) ?>">
            <?= csrf_field() ?>
            <div>
                <label for="codigo">Código de reserva</label>
                <input class="input" type="text" id="codigo" name="codigo" placeholder="CC-XXXXXX" value="<?= e($codigoIn) ?>" autocomplete="off" required style="text-transform:uppercase">
            </div>
            <div>
                <label for="cedula">Número de cédula</label>
                <input class="input" type="text" id="cedula" name="cedula" inputmode="numeric" required>
            </div>
            <div><button class="btn" type="submit">Consultar</button></div>
        </form>
        <p class="note">¿Todavía no reservaste? <a href="<?= e(url('reservar.php')) ?>">Reserva tus entradas</a>.</p>
    </div>
<?php endif; ?>
</div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
