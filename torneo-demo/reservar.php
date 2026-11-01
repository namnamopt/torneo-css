<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
start_session();

$P  = partido();
$ts = partido_ts();
$S  = sectores();
$X  = extras();
$disp = disponibles();

$hoy   = date('Y-m-d');
$limite = date('Y-m-d', $ts);
if ($limite < $hoy) {
    $limite = $hoy; // si la fecha del partido (config.php) ya pasó, el formulario sigue funcionando para la demo
}

$pageTitle = 'Reservar entradas';
$pageDesc  = 'Reserva tus entradas y canjéalas en el centro con tu cédula y tu código.';
$active    = 'reservar';

$errores = [];
$old = [
    'nombre' => '', 'apellido' => '', 'cedula' => '', 'telefono' => '', 'correo' => '',
    'fecha_retiro' => '', 'sector' => clean_text($_GET['sector'] ?? '', 20), 'cantidad' => '1',
    'condicion' => '', 'extras' => [], 'comentario' => '',
];
if (!isset($S[$old['sector']])) {
    $old['sector'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nombre'       => clean_text($_POST['nombre'] ?? '', 60),
        'apellido'     => clean_text($_POST['apellido'] ?? '', 60),
        'cedula'       => clean_text($_POST['cedula'] ?? '', 20),
        'telefono'     => clean_text($_POST['telefono'] ?? '', 25),
        'correo'       => clean_text($_POST['correo'] ?? '', 120),
        'fecha_retiro' => clean_text($_POST['fecha_retiro'] ?? '', 10),
        'sector'       => clean_text($_POST['sector'] ?? '', 20),
        'cantidad'     => clean_text($_POST['cantidad'] ?? '1', 3),
        'condicion'    => clean_text($_POST['condicion'] ?? '', 10),
        'extras'       => array_values(array_intersect(array_keys($X), array_map('strval', (array) ($_POST['extras'] ?? [])))),
        'comentario'   => clean_text($_POST['comentario'] ?? '', 500),
    ];

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    }
    if (!empty($_POST['website'])) {
        $errores[] = 'Envío rechazado.';
    }
    if (mb_strlen($old['nombre']) < 2)   { $errores[] = 'Escribe tu nombre.'; }
    if (mb_strlen($old['apellido']) < 2) { $errores[] = 'Escribe tu apellido.'; }
    $ced = clean_cedula($old['cedula']);
    if (strlen($ced) < 5 || strlen($ced) > 10) { $errores[] = 'La cédula debe tener entre 5 y 10 dígitos.'; }
    if (clean_phone($old['telefono']) === '') { $errores[] = 'El teléfono no parece válido.'; }
    if (clean_email($old['correo']) === '')   { $errores[] = 'Escribe un correo válido.'; }
    $dt = DateTime::createFromFormat('Y-m-d', $old['fecha_retiro']);
    if (!$dt || $dt->format('Y-m-d') !== $old['fecha_retiro']) {
        $errores[] = 'Elige una fecha de retiro válida.';
    } elseif ($old['fecha_retiro'] < $hoy || $old['fecha_retiro'] > $limite) {
        $errores[] = 'La fecha de retiro debe estar entre hoy y el día del partido.';
    }
    if (!isset($S[$old['sector']])) { $errores[] = 'Selecciona un sector.'; }
    $cant = (int) $old['cantidad'];
    if ($cant < 1 || $cant > MAX_POR_RESERVA) { $errores[] = 'La cantidad de entradas debe estar entre 1 y ' . MAX_POR_RESERVA . '.'; }
    if (!in_array($old['condicion'], ['socio', 'no_socio'], true)) { $errores[] = 'Indica si eres socio o no socio.'; }
    if (!$errores && !throttle('reserva', 8)) {
        $errores[] = 'Espera unos segundos antes de enviar otra reserva.';
    }

    if (!$errores) {
        $r = reserva_crear([
            'nombre'       => $old['nombre'],
            'apellido'     => $old['apellido'],
            'cedula'       => $ced,
            'telefono'     => clean_phone($old['telefono']),
            'correo'       => clean_email($old['correo']),
            'fecha_retiro' => $old['fecha_retiro'],
            'sector'       => $old['sector'],
            'cantidad'     => $cant,
            'condicion'    => $old['condicion'],
            'extras'       => $old['extras'],
            'comentario'   => $old['comentario'],
        ]);
        if ($r['ok']) {
            if (MAIL_TO !== '' && function_exists('mail')) {
                $safe = fn($s) => trim(preg_replace('/[\r\n]+/', ' ', (string) $s) ?? '');
                @mail(MAIL_TO, '[' . SITE_NAME . '] Nueva reserva ' . $r['codigo'],
                    "Reserva {$r['codigo']}\nNombre: {$safe($old['nombre'])} {$safe($old['apellido'])}\nCédula: {$ced}\nSector: {$S[$old['sector']]['nombre']} x{$cant}\nRetiro: {$old['fecha_retiro']}");
            }
            $_SESSION['ultima_reserva'] = $r['codigo'];
            header('Location: ' . url('mi-reserva.php') . '?c=' . urlencode($r['codigo']) . '&nueva=1');
            exit;
        }
        $errores[] = $r['error'];
        $disp = disponibles();
    }
}

$cfg = ['max' => MAX_POR_RESERVA, 'sectores' => []];
foreach ($S as $k => $s) {
    $cfg['sectores'][$k] = ['nombre' => $s['nombre'], 'general' => $s['general'], 'socio' => $s['socio'], 'disp' => $disp[$k]];
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p class="eyebrow">// RESERVA EN LÍNEA</p>
        <h1 class="h-xl">Reservar entradas</h1>
        <p class="lead"><?= e($P['local']) ?> vs <?= e($P['visita']) ?> · <?= e(fecha_es($ts)) ?>, <?= e(date('H:i', $ts)) ?> hs. Reserva ahora y canjea tu ticket en el centro en la fecha de retiro que elijas.</p>
    </div>
</section>

<div class="wrap res-layout">
    <form class="form" id="form-reserva" method="post" action="<?= e(url('reservar.php')) ?>" novalidate>
        <?php if ($errores): ?>
            <div class="alert alert--err" role="alert" id="errores">
                <strong>Revisa estos datos:</strong>
                <ul><?php foreach ($errores as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>No completar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        <section class="form-block">
            <h2><i>01</i> Tus datos</h2>
            <div class="form__row">
                <div><label for="nombre">Nombre</label><input class="input" type="text" id="nombre" name="nombre" value="<?= e($old['nombre']) ?>" autocomplete="given-name" required></div>
                <div><label for="apellido">Apellido</label><input class="input" type="text" id="apellido" name="apellido" value="<?= e($old['apellido']) ?>" autocomplete="family-name" required></div>
            </div>
            <div class="form__row">
                <div>
                    <label for="cedula">Número de cédula</label>
                    <input class="input" type="text" id="cedula" name="cedula" value="<?= e($old['cedula']) ?>" inputmode="numeric" required>
                    <p class="field-hint">La necesitarás para canjear y consultar tu reserva.</p>
                </div>
                <div><label for="telefono">Teléfono</label><input class="input" type="tel" id="telefono" name="telefono" value="<?= e($old['telefono']) ?>" autocomplete="tel" required></div>
            </div>
            <div class="form__row">
                <div><label for="correo">Correo electrónico</label><input class="input" type="email" id="correo" name="correo" value="<?= e($old['correo']) ?>" autocomplete="email" required></div>
                <div>
                    <label for="fecha_retiro">Fecha de retiro</label>
                    <input class="input" type="date" id="fecha_retiro" name="fecha_retiro" value="<?= e($old['fecha_retiro']) ?>" min="<?= e($hoy) ?>" max="<?= e($limite) ?>" required>
                    <p class="field-hint">Día en que pasarás por el centro a canjear.</p>
                </div>
            </div>
        </section>

        <section class="form-block">
            <h2><i>02</i> Sector</h2>
            <fieldset>
                <legend class="sr">Sector</legend>
                <div class="tiles">
                    <?php foreach ($S as $k => $s): $d = $disp[$k]; ?>
                        <label class="tile">
                            <input type="radio" name="sector" value="<?= e($k) ?>" <?= $old['sector'] === $k ? 'checked' : '' ?> <?= $d <= 0 ? 'disabled' : '' ?> required>
                            <span class="tile__in" style="--c:<?= e($s['color']) ?>">
                                <b><?= e($s['nombre']) ?></b>
                                <small><?= e($s['desc']) ?></small>
                                <em><?= e(fmt_num($s['general'])) ?> · socio <?= e(fmt_num($s['socio'])) ?></em>
                                <em style="color:<?= $d > 0 ? 'var(--mute)' : 'var(--warn)' ?>"><?= $d > 0 ? e(fmt_num($d)) . ' disponibles' : 'Agotado' ?></em>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        </section>

        <section class="form-block">
            <h2><i>03</i> Entradas y condición</h2>
            <div class="form__row">
                <div>
                    <label for="cantidad">Cantidad de entradas</label>
                    <div class="stepper">
                        <button type="button" data-step="-1" aria-label="Una entrada menos">−</button>
                        <input class="input" type="number" id="cantidad" name="cantidad" min="1" max="<?= (int) MAX_POR_RESERVA ?>" value="<?= e($old['cantidad'] ?: '1') ?>" required>
                        <button type="button" data-step="1" aria-label="Una entrada más">+</button>
                    </div>
                    <p class="field-hint">Máximo <?= (int) MAX_POR_RESERVA ?> por reserva.</p>
                </div>
                <fieldset>
                    <legend>Condición</legend>
                    <div class="seg">
                        <label class="tile"><input type="radio" name="condicion" value="socio" <?= $old['condicion'] === 'socio' ? 'checked' : '' ?> required><span class="tile__in"><b>Socio</b></span></label>
                        <label class="tile"><input type="radio" name="condicion" value="no_socio" <?= $old['condicion'] === 'no_socio' ? 'checked' : '' ?> required><span class="tile__in"><b>No socio</b></span></label>
                    </div>
                </fieldset>
            </div>
            <fieldset>
                <legend>Extras (opcional)</legend>
                <div class="chips">
                    <?php foreach ($X as $k => $label): ?>
                        <label class="tile"><input type="checkbox" name="extras[]" value="<?= e($k) ?>" <?= in_array($k, $old['extras'], true) ? 'checked' : '' ?>><span class="tile__in"><b><?= e($label) ?></b></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <div>
                <label for="comentario">Comentario libre</label>
                <textarea class="input" id="comentario" name="comentario" rows="4" maxlength="500" placeholder="Escribe aquí cualquier observación..."><?= e($old['comentario']) ?></textarea>
            </div>
        </section>

        <div style="display:flex; gap:.8rem; flex-wrap:wrap">
            <button class="btn" type="submit">Enviar reserva</button>
            <button class="btn btn--ghost" type="reset" id="btn-reset">Limpiar todo</button>
        </div>
    </form>

    <aside class="res-side" aria-label="Resumen de tu reserva">
        <div class="map-card">
            <?= stadium_svg($disp, $old['sector']) ?>
            <p class="note">Toca un sector del esquema para elegirlo</p>
        </div>
        <div class="sumbox">
            <h2>Resumen</h2>
            <dl class="sum">
                <div><dt>Sector</dt><dd data-sum="sector">—</dd></div>
                <div><dt>Disponibilidad</dt><dd data-sum="disp">—</dd></div>
                <div><dt>Condición</dt><dd data-sum="cond">—</dd></div>
                <div><dt>Precio por entrada</dt><dd data-sum="unit">—</dd></div>
                <div><dt>Cantidad</dt><dd data-sum="cant">1</dd></div>
                <div class="sum__total"><dt>Total a abonar al retirar</dt><dd data-sum="total">—</dd></div>
            </dl>
            <p class="note">El pago se realiza en el centro al canjear tu entrada.</p>
        </div>
    </aside>
</div>

<script type="application/json" id="cfg"><?= json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php
$extraScripts = '<script src="' . e(asset('js/reservar.js')) . '"></script>';
require __DIR__ . '/includes/footer.php';
?>
