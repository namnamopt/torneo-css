<?php
/**
 * Variables opcionales antes de incluir:
 *   $pageTitle, $pageDesc, $active, $bodyClass, $extraHead
 */
start_session();
$pageTitle = $pageTitle ?? SITE_NAME;
$active    = $active ?? '';
$P = partido();
$nav = [
    'index'     => ['Inicio',       'index.php'],
    'sectores'  => ['Sectores',     'sectores.php'],
    'llegar'    => ['Cómo llegar',  'como-llegar.php'],
    'mireserva' => ['Mi reserva',   'mi-reserva.php'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · <?= e(SITE_NAME) ?></title>
    <meta name="description" content="<?= e($pageDesc ?? SITE_TAGLINE) ?>">
    <meta name="theme-color" content="#060a18">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,500;0,600;0,700;0,800;1,700;1,800&family=Barlow:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip" href="#main">Saltar al contenido</a>

<header class="topbar">
    <div class="topbar__in">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="<?= e(SITE_NAME) ?> · inicio">
            <span class="brand__mark" aria-hidden="true"></span>
            <span class="brand__txt">CENTRO<b>//</b>CANJE</span>
        </a>

        <button class="burger" id="burger" aria-expanded="false" aria-controls="nav" aria-label="Abrir menú">
            <span></span><span></span><span></span>
        </button>

        <nav class="nav" id="nav" aria-label="Principal">
            <?php foreach ($nav as $key => [$label, $file]): ?>
                <a href="<?= e(url($file)) ?>" class="<?= $active === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
            <a class="btn btn--sm nav__cta" href="<?= e(url('reservar.php')) ?>">Reservar entradas</a>
        </nav>
    </div>
</header>

<main id="main">
