<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$P = partido();
$B = $P['boleteria'];
$pageTitle = 'Cómo llegar y acceder';
$pageDesc  = 'Procedimiento para llegar y acceder al estadio, plano de sectores y datos de boletería.';
$active    = 'llegar';

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p class="eyebrow">// DÍA DEL PARTIDO</p>
        <h1 class="h-xl">Procedimiento para llegar y acceder al estadio</h1>
        <p class="lead">Siete pasos desde que llegas hasta que encuentras tu asiento.</p>
    </div>
</section>

<section class="section">
    <div class="wrap two-col">
        <div>
            <ol class="tl">
                <?php foreach (pasos_acceso() as $i => $paso): ?>
                    <li class="reveal"><p><?= e($paso) ?></p></li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div>
            <figure class="plano reveal" id="plano">
                <img src="<?= e(url('src/img/defensores.map.jpeg')) ?>" alt="Plano del Estadio Defensores del Chaco con distribución de sectores">
                <figcaption>
                    Distribución de sectores: Platea Preferencial al centro, Palcos en laterales VIP,
                    Graderías Norte y Sur populares, y Sector Visitante habilitado para hinchada rival.
                </figcaption>
            </figure>
            <p style="margin-top:1.2rem">
                <a class="btn btn--ghost" href="<?= e($P['maps']) ?>" target="_blank" rel="noopener noreferrer">Ver ubicación en Google Maps</a>
            </p>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="wrap">
        <div class="section__head">
            <div>
                <p class="eyebrow">// CENTRO DE CANJE</p>
                <h2 class="h-caps">Dónde y cuándo retirar tus entradas</h2>
            </div>
        </div>
        <div class="info-grid">
            <div class="card info">
                <h3>Dirección de boletería</h3>
                <p><?= e($B['direccion']) ?></p>
            </div>
            <div class="card info">
                <h3>Horario de atención</h3>
                <p><?= e($B['horario']) ?></p>
            </div>
            <div class="card info">
                <h3>Contacto</h3>
                <p><a href="mailto:<?= e($B['correo']) ?>"><?= e($B['correo']) ?></a><br><a href="tel:<?= e($B['tel_href']) ?>"><?= e($B['telefono']) ?></a></p>
            </div>
        </div>
    </div>
</section>

<section class="section cta">
    <div class="wrap cta__in">
        <div>
            <p class="eyebrow">// ¿TODAVÍA SIN ENTRADA?</p>
            <h2 class="h-xl">Reserva y retira en el centro</h2>
        </div>
        <a class="btn" href="<?= e(url('reservar.php')) ?>">Reservar entradas</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
