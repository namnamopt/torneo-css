<?php
declare(strict_types=1);

/**
 * Centro de Canje · configuración y utilidades compartidas.
 * Compatible con PHP 7.4+ (XAMPP). No necesita MySQL: las reservas se guardan en data/reservas.json.
 */

mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Asuncion');

const SITE_NAME    = 'Centro de Canje';
const SITE_TAGLINE = 'Reserva y canje de entradas · Torneo Clausura';
// Si configuras el correo en XAMPP (sendmail/SMTP) pon aquí tu dirección para recibir avisos de nueva reserva.
const MAIL_TO      = '';
const MAX_POR_RESERVA = 10;

define('DATA_DIR', __DIR__ . '/data');

// URL base calculada automáticamente (ej.: /torneo-demo)
$__doc  = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$__root = str_replace('\\', '/', (string) realpath(__DIR__));
define('BASE_URL', ($__doc !== '' && strpos($__root, $__doc) === 0) ? rtrim(substr($__root, strlen($__doc)), '/') : '');

/* ==========================================================================
   Datos del partido y catálogos  (edita aquí)
   ========================================================================== */

function partido(): array
{
    return [
        'local'    => 'Olimpia',
        'visita'   => 'Cerro',
        'torneo'   => 'Torneo Clausura',
        'estadio'  => 'Estadio Defensores del Chaco',
        // Fecha y hora del partido (hora de Asunción). Cámbiala por la del partido real.
        'fecha'    => '2026-11-14 18:30',
        'boleteria' => [
            'direccion' => 'Av. General Artigas, Asunción, Paraguay',
            'horario'   => 'Lunes a Sábados de 08:00 a 18:00',
            'correo'    => 'boleteria@defensoresdelchaco.com.py',
            'telefono'  => '+595 21 212 345',
            'tel_href'  => '+59521212345',
        ],
        'maps' => 'https://www.google.com/maps/search/?api=1&query=Estadio+Defensores+del+Chaco,Asuncion,Paraguay',
    ];
}

function partido_ts(): int
{
    return (int) strtotime(partido()['fecha']);
}

/** Sectores del estadio. La clave coincide con el value del formulario original. */
function sectores(): array
{
    return [
        'platea' => [
            'nombre' => 'Platea Preferencial', 'desc' => 'Ubicación central con visión panorámica',
            'cap' => 4200, 'general' => 250000, 'socio' => 180000, 'color' => '#ffc531', 'tipo' => 'Premium',
        ],
        'palco' => [
            'nombre' => 'Palco', 'desc' => 'Sector VIP con servicios',
            'cap' => 1500, 'general' => 400000, 'socio' => 300000, 'color' => '#c58bff', 'tipo' => 'VIP',
        ],
        'norte' => [
            'nombre' => 'Gradería Norte', 'desc' => 'Tribuna popular Norte',
            'cap' => 9800, 'general' => 100000, 'socio' => 70000, 'color' => '#4cc2ff', 'tipo' => 'Popular',
        ],
        'sur' => [
            'nombre' => 'Gradería Sur', 'desc' => 'Tribuna popular Sur',
            'cap' => 9800, 'general' => 100000, 'socio' => 70000, 'color' => '#ff8a5c', 'tipo' => 'Popular',
        ],
        'visitante' => [
            'nombre' => 'Sector Visitante', 'desc' => 'Ubicación para la hinchada rival',
            'cap' => 6000, 'general' => 120000, 'socio' => 120000, 'color' => '#2fe39a', 'tipo' => 'Visitante',
        ],
    ];
}

function extras(): array
{
    return [
        'estacionamiento' => 'Estacionamiento',
        'camiseta'        => 'Camiseta',
        'programa'        => 'Programa',
    ];
}

function pasos_acceso(): array
{
    return [
        'Estacionar el vehículo en el predio habilitado o bajarse del transporte público en la parada más cercana.',
        'Dirigirse a la entrada que corresponda según el sector indicado en la entrada (Norte, Sur, Preferencial, Visitante).',
        'Presentar la entrada impresa o digital en la primera verificación del personal de seguridad.',
        'Pasar el control de bolso y mochilas en las requisas.',
        'Validar la entrada en los torniquetes electrónicos o lectores de códigos QR.',
        'Ingresar en el estadio y seguir las señales hacia la gradería o sector.',
        'Ubicar fila y el número de asiento indicado en el ticket.',
    ];
}

/* ==========================================================================
   Utilidades generales
   ========================================================================== */

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('src/' . ltrim($path, '/')) . '?v=' . (string) @filemtime(__DIR__ . '/src/' . ltrim($path, '/'));
}

function fmt_gs(int $n): string
{
    return number_format($n, 0, ',', '.') . ' Gs.';
}

function fmt_num(int $n): string
{
    return number_format($n, 0, ',', '.');
}

function fecha_es(int $ts, bool $conDia = true): string
{
    $dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $s = (int) date('j', $ts) . ' de ' . $meses[(int) date('n', $ts) - 1];
    return $conDia ? ucfirst($dias[(int) date('w', $ts)]) . ' ' . $s : $s;
}

function clean_text($s, int $max = 200): string
{
    $s = trim((string) $s);
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s) ?? '';
    return mb_substr($s, 0, $max);
}

function clean_email(string $s): string
{
    $s = trim($s);
    return filter_var($s, FILTER_VALIDATE_EMAIL) ? mb_strtolower($s) : '';
}

function clean_phone(string $s): string
{
    $s = trim($s);
    return preg_match('/^\+?[0-9 ()\-]{6,20}$/', $s) ? $s : '';
}

function clean_cedula(string $s): string
{
    return preg_replace('/\D+/', '', $s) ?? '';
}

/* ---------- Sesión, CSRF y límite de envíos ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check($t): bool
{
    start_session();
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals((string) $_SESSION['csrf'], $t);
}

function throttle(string $key, int $seconds): bool
{
    start_session();
    $now = time();
    if (isset($_SESSION['thr'][$key]) && $now - (int) $_SESSION['thr'][$key] < $seconds) {
        return false;
    }
    $_SESSION['thr'][$key] = $now;
    return true;
}

/* ==========================================================================
   Reservas (archivo JSON con bloqueo)
   ========================================================================== */

function reservas_load(): array
{
    $f = DATA_DIR . '/reservas.json';
    if (!is_file($f)) {
        return [];
    }
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d) ? $d : [];
}

/** Entradas reservadas (no canceladas) por sector. */
function reservadas_por_sector(?array $reservas = null): array
{
    $reservas = $reservas ?? reservas_load();
    $out = array_fill_keys(array_keys(sectores()), 0);
    foreach ($reservas as $r) {
        if (($r['estado'] ?? '') !== 'cancelada' && isset($out[$r['sector'] ?? ''])) {
            $out[$r['sector']] += (int) $r['cantidad'];
        }
    }
    return $out;
}

/** Entradas disponibles por sector. */
function disponibles(?array $reservas = null): array
{
    $res = reservadas_por_sector($reservas);
    $out = [];
    foreach (sectores() as $k => $s) {
        $out[$k] = max(0, $s['cap'] - $res[$k]);
    }
    return $out;
}

function precio_unitario(string $sector, string $condicion): int
{
    $s = sectores()[$sector];
    return (int) ($condicion === 'socio' ? $s['socio'] : $s['general']);
}

function codigo_reserva(array $existentes): string
{
    $alf = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // sin 0/O/1/I/L
    do {
        $c = '';
        for ($i = 0; $i < 6; $i++) {
            $c .= $alf[random_int(0, strlen($alf) - 1)];
        }
        $c = 'CC-' . $c;
    } while (in_array($c, $existentes, true));
    return $c;
}

/**
 * Guarda una reserva comprobando la disponibilidad dentro del bloqueo del archivo.
 * @return array ['ok'=>bool, 'codigo'=>string, 'error'=>string]
 */
function reserva_crear(array $d): array
{
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0775, true);
    }
    $f = DATA_DIR . '/reservas.json';
    $fh = @fopen($f, 'c+');
    if (!$fh) {
        return ['ok' => false, 'codigo' => '', 'error' => 'No se pudo guardar la reserva. Verifica que la carpeta data/ sea escribible.'];
    }
    flock($fh, LOCK_EX);
    $raw = stream_get_contents($fh);
    $lista = json_decode((string) $raw, true);
    if (!is_array($lista)) {
        $lista = [];
    }

    $disp = disponibles($lista);
    if ($d['cantidad'] > $disp[$d['sector']]) {
        flock($fh, LOCK_UN);
        fclose($fh);
        $n = $disp[$d['sector']];
        return ['ok' => false, 'codigo' => '', 'error' => $n > 0
            ? "Solo quedan {$n} entradas disponibles en " . sectores()[$d['sector']]['nombre'] . '.'
            : sectores()[$d['sector']]['nombre'] . ' está agotado.'];
    }

    $codigo = codigo_reserva(array_column($lista, 'codigo'));
    $unit = precio_unitario($d['sector'], $d['condicion']);
    $d['id']          = bin2hex(random_bytes(6));
    $d['codigo']      = $codigo;
    $d['fecha']       = date('c');
    $d['estado']      = 'pendiente';
    $d['precio_unit'] = $unit;
    $d['total']       = $unit * $d['cantidad'];
    $lista[] = $d;

    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, (string) json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    return ['ok' => true, 'codigo' => $codigo, 'error' => ''];
}

function reserva_buscar(string $codigo): ?array
{
    $codigo = strtoupper(trim($codigo));
    foreach (reservas_load() as $r) {
        if (($r['codigo'] ?? '') === $codigo) {
            return $r;
        }
    }
    return null;
}

/* ==========================================================================
   Gráficos SVG generados en servidor
   ========================================================================== */

/** Código de barras decorativo, determinista a partir de un texto. */
function barcode_svg(string $seed, bool $vertical = false): string
{
    $h = hash('sha256', $seed);
    $pos = 0;
    $bars = [];
    for ($i = 0; $i < 38; $i++) {
        $w = 1 + (hexdec($h[$i]) % 3);
        $gap = 1 + (hexdec($h[($i + 24) % 64]) % 2);
        $bars[] = [$pos, $w];
        $pos += $w + $gap;
    }
    $total = $pos;
    $rects = '';
    foreach ($bars as [$p, $w]) {
        $rects .= $vertical
            ? '<rect x="0" y="' . $p . '" width="40" height="' . $w . '"/>'
            : '<rect x="' . $p . '" y="0" width="' . $w . '" height="40"/>';
    }
    $vb = $vertical ? "0 0 40 {$total}" : "0 0 {$total} 40";
    return '<svg class="barcode barcode--' . ($vertical ? 'v' : 'h') . '" viewBox="' . $vb . '" preserveAspectRatio="none" fill="currentColor" aria-hidden="true" focusable="false">' . $rects . '</svg>';
}

/**
 * Esquema ilustrativo del estadio con los 5 sectores (interactivo si se agrega el JS correspondiente).
 * @param array $disp   disponibles por sector
 * @param string $sel   sector seleccionado
 */
function stadium_svg(array $disp = [], string $sel = ''): string
{
    $S = sectores();
    // [puntos del polígono, x del texto, y del texto]
    $forma = [
        'palco'     => ['148,46 452,46 432,98 168,98', 300, 77],
        'platea'    => ['168,342 432,342 456,394 144,394', 300, 373],
        'norte'     => ['44,100 132,124 132,316 44,340', 88, 222],
        'visitante' => ['468,124 556,100 556,206 468,206', 512, 158],
        'sur'       => ['468,218 556,218 556,340 468,316', 512, 282],
    ];
    $out = '<svg class="stadium" viewBox="0 0 600 440" role="group" aria-label="Esquema ilustrativo del estadio con sus sectores">';
    // Anillo exterior y campo
    $out .= '<rect class="st-ring" x="20" y="24" width="560" height="392" rx="150"/>';
    $out .= '<g class="st-pitch"><rect x="150" y="112" width="300" height="218" rx="6"/><line x1="300" y1="112" x2="300" y2="330"/>'
          . '<circle cx="300" cy="221" r="34"/><circle cx="300" cy="221" r="2.5" class="fill"/>'
          . '<rect x="150" y="167" width="52" height="108"/><rect x="398" y="167" width="52" height="108"/>'
          . '<rect x="150" y="193" width="20" height="56"/><rect x="430" y="193" width="20" height="56"/></g>';
    foreach ($forma as $k => [$pts, $tx, $ty]) {
        $s = $S[$k];
        $d = $disp[$k] ?? $s['cap'];
        $cls = 'stand' . ($sel === $k ? ' is-sel' : '') . ($d <= 0 ? ' is-out' : '');
        $label = $s['nombre'] . ' · ' . ($d > 0 ? fmt_num((int) $d) . ' disponibles' : 'agotado');
        $vert = ($k === 'norte' || $k === 'sur' || $k === 'visitante');
        $txt = $k === 'visitante' ? 'VISIT.' : strtoupper($k === 'platea' ? 'Platea' : $k);
        $rot = ($k === 'norte') ? -90 : (($k === 'sur' || $k === 'visitante') ? 90 : 0);
        $out .= '<g class="' . $cls . '" data-sector="' . e($k) . '" style="--c:' . e($s['color']) . '" tabindex="0" role="button" aria-label="' . e($label) . '" aria-pressed="' . ($sel === $k ? 'true' : 'false') . '">'
              . '<polygon points="' . $pts . '"/>'
              . '<text x="' . $tx . '" y="' . $ty . '" text-anchor="middle" dominant-baseline="middle"' . ($rot ? ' transform="rotate(' . $rot . ' ' . $tx . ' ' . $ty . ')"' : '') . '>' . e($txt) . '</text>'
              . '<title>' . e($label) . '</title></g>';
    }
    $out .= '</svg>';
    return $out;
}
