<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$P  = partido();
$ts = partido_ts();
$S  = sectores();
$pageTitle = $P['local'] . ' vs ' . $P['visita'];
$pageDesc  = 'Reserva tus entradas para ' . $P['local'] . ' vs ' . $P['visita'] . ' y canjéalas en el centro con tu cédula y tu código.';
$active    = 'index';

$reservas = reservas_load();
$disp     = disponibles($reservas);
$capTotal = array_sum(array_column($S, 'cap'));
$dispTotal = array_sum($disp);
$nReservas = count(array_filter($reservas, fn($r) => ($r['estado'] ?? '') !== 'cancelada'));
$minPrecio = min(array_column($S, 'socio'));

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero__bg" aria-hidden="true"></div>
    <div class="wrap hero__in">
        <div class="hero__copy">
            <p class="eyebrow">// <?= e(mb_strtoupper($P['torneo'])) ?> · CENTRO DE CANJE</p>
            <h1 class="hero__title"><?= e($P['local']) ?><span class="vs">vs</span><?= e($P['visita']) ?></h1>
            <div class="hero__meta">
                <span><b><?= e(fecha_es($ts)) ?></b></span>
                <span><b><?= e(date('H:i', $ts)) ?> hs</b></span>
                <span><?= e($P['estadio']) ?></span>
            </div>
            <p class="hero__lead">Reserva tu entrada desde casa, elige tu sector en el plano y canjéala en el centro con tu cédula y tu código de reserva.</p>
            <div class="hero__cta">
                <a class="btn" href="<?= e(url('reservar.php')) ?>">Reservar entradas</a>
                <a class="btn btn--ghost" href="<?= e(url('sectores.php')) ?>">Ver sectores y precios</a>
            </div>
        </div>

        <div class="ticket-wrap">
            <article class="ticket" aria-label="Entrada de demostración del partido">
                <div class="ticket__main">
                    <p class="eyebrow"><?= e($P['torneo']) ?></p>
                    <p class="ticket__teams"><?= e($P['local']) ?><span>vs</span><?= e($P['visita']) ?></p>
                    <dl class="ticket__list">
                        <div><dt>Fecha</dt><dd><?= e(fecha_es($ts)) ?></dd></div>
                        <div><dt>Hora</dt><dd><?= e(date('H:i', $ts)) ?> hs</dd></div>
                        <div class="wide"><dt>Estadio</dt><dd><?= e($P['estadio']) ?></dd></div>
                    </dl>
                    <div class="countdown" data-countdown="<?= e(date('c', $ts)) ?>" role="timer" aria-label="Tiempo restante para el partido">
                        <div><b data-u="d">--</b><span>días</span></div>
                        <div><b data-u="h">--</b><span>horas</span></div>
                        <div><b data-u="m">--</b><span>min</span></div>
                        <div><b data-u="s">--</b><span>seg</span></div>
                    </div>
                </div>
                <div class="ticket__stub">
                    <span class="ticket__admit">Admit one</span>
                    <?= barcode_svg('hero-' . $P['fecha'], true) ?>
                    <?= barcode_svg('hero-' . $P['fecha'], false) ?>
                    <span class="ticket__serial">Nº 000-DEMO</span>
                </div>
            </article>
        </div>
    </div>

    <div class="wrap">
        <dl class="stats">
            <div><dt>Capacidad total</dt><dd><?= e(fmt_num($capTotal)) ?></dd></div>
            <div><dt>Entradas disponibles</dt><dd><?= e(fmt_num($dispTotal)) ?></dd></div>
            <div><dt>Reservas hechas</dt><dd><?= e(fmt_num($nReservas)) ?></dd></div>
            <div><dt>Desde (socio)</dt><dd><?= e(fmt_num($minPrecio)) ?><small>Gs.</small></dd></div>
        </dl>
    </div>
</section>

<!-- CÓMO FUNCIONA -->
<section class="section">
    <div class="wrap">
        <div class="section__head">
            <div>
                <p class="eyebrow">// CÓMO FUNCIONA EL CANJE</p>
                <h2 class="h-caps">Cuatro pasos, cero filas</h2>
            </div>
            <a class="link-arrow" href="<?= e(url('como-llegar.php')) ?>">Cómo llegar y acceder</a>
        </div>
        <ol class="steps">
            <li class="card step reveal">
                <span class="step__n">01</span>
                <h3>Elige tu sector</h3>
                <p>Compara precios de socio y no socio y mira la disponibilidad real de cada tribuna.</p>
            </li>
            <li class="card step reveal">
                <span class="step__n">02</span>
                <h3>Reserva en línea</h3>
                <p>Completa el formulario, indica la fecha en que pasarás por el centro y recibe tu código.</p>
            </li>
            <li class="card step reveal">
                <span class="step__n">03</span>
                <h3>Pasa por el centro</h3>
                <p>Ven en la fecha de retiro, en horario de boletería, con tu cédula y tu código de reserva.</p>
            </li>
            <li class="card step reveal">
                <span class="step__n">04</span>
                <h3>Canjea tu ticket</h3>
                <p>Cambiamos tu reserva por la entrada. Ya solo queda llegar, validar en el torniquete y alentar.</p>
            </li>
        </ol>
    </div>
</section>

<!-- SECTORES -->
<section class="section section--alt">
    <div class="wrap">
        <div class="section__head">
            <div>
                <p class="eyebrow">// SECTORES DEL ESTADIO</p>
                <h2 class="h-caps">Elige dónde vivir el partido</h2>
            </div>
            <a class="link-arrow" href="<?= e(url('sectores.php')) ?>">Ver detalle completo</a>
        </div>
        <div class="sgrid">
            <?php foreach ($S as $k => $s): ?>
                <a class="card sc reveal" style="--c:<?= e($s['color']) ?>" href="<?= e(url('reservar.php?sector=' . $k)) ?>">
                    <span class="sc__tag"><?= e($s['tipo']) ?></span>
                    <h3><?= e($s['nombre']) ?></h3>
                    <p><?= e($s['desc']) ?></p>
                    <span class="sc__price"><?= e(fmt_num($s['general'])) ?><small>Gs. · precio general</small></span>
                    <span class="sc__socio">Socio: <?= e(fmt_gs($s['socio'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PLANO -->
<section class="section feature">
    <div class="wrap feature__in">
        <div class="feature__copy">
            <p class="eyebrow">// PLANO DEL ESTADIO</p>
            <h2 class="h-xl">Llega por la entrada correcta</h2>
            <p>Cada sector tiene su propio acceso. Revisa el plano de calles y entradas del <?= e($P['estadio']) ?> antes de salir de casa.</p>
            <ul class="checks">
                <li>Platea y Preferencia: acceso por las entradas 1 a 4 y 7 a 10</li>
                <li>Graderías Norte y Sur con accesos propios</li>
                <li>Sector Visitante habilitado para la hinchada rival</li>
            </ul>
            <a class="link-arrow" href="<?= e(url('como-llegar.php')) ?>">Ver plano y procedimiento</a>
        </div>
        <figure class="plano reveal">
            <img src="<?= e(url('src/img/defensores.map.jpeg')) ?>" alt="Plano del Estadio Defensores del Chaco con distribución de sectores" loading="lazy">
            <figcaption>Distribución de sectores y accesos alrededor del estadio.</figcaption>
        </figure>
    </div>
</section>

<!-- EXTRAS -->
<section class="section feature feature--rev">
    <div class="wrap feature__in">
        <div class="feature__copy">
            <p class="eyebrow">// EXTRAS</p>
            <h2 class="h-xl">Suma algo más a tu reserva</h2>
            <p>Al reservar puedes pedir que te dejemos listos el <b>estacionamiento</b>, la <b>camiseta</b> o el <b>programa</b> del partido. Los extras se retiran y abonan junto con tu entrada.</p>
            <a class="link-arrow" href="<?= e(url('reservar.php')) ?>">Reservar ahora</a>
        </div>
        <div class="map-card reveal">
            <?= stadium_svg($disp) ?>
            <p class="note">Esquema ilustrativo · elige tu sector al reservar</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section cta">
    <div class="wrap cta__in">
        <div>
            <p class="eyebrow">// NO TE QUEDES AFUERA</p>
            <h2 class="h-xl">Asegura tu lugar para <?= e($P['local']) ?> vs <?= e($P['visita']) ?></h2>
        </div>
        <a class="btn" href="<?= e(url('reservar.php')) ?>">Reservar entradas</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
