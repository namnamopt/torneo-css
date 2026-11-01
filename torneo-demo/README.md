# Centro de Canje · reserva y canje de entradas (PHP, para XAMPP)

## Instalación en XAMPP
1. Copia la carpeta `torneo-demo` a `C:\xampp\htdocs\`.
2. Inicia **Apache** desde el panel de XAMPP (MySQL no hace falta).
3. Entra a **http://localhost/torneo-demo/**

Requisitos: PHP 7.4+ y carpeta `data/` escribible. Las fuentes vienen de Google Fonts (con internet).

## Páginas
- `index.php` Inicio: partido, cuenta regresiva, pasos del canje, sectores, plano
- `sectores.php` Tabla de sectores, precios general/socio, disponibilidad, esquema del estadio
- `reservar.php` Formulario con resumen en vivo y selección en el esquema
- `mi-reserva.php` Ticket de la reserva y consulta por código + cédula
- `como-llegar.php` Procedimiento de acceso, plano, datos de boletería

## Qué editar
- `config.php` → `partido()`: equipos, **fecha y hora**, boletería; `sectores()`: capacidad y precios; `extras()`.
- Las reservas se guardan en `data/reservas.json` (protegido con `.htaccess`). Para reiniciar la demo déjalo como `[]`.
- `legacy/` conserva el HTML original.

## Seguridad
Salida escapada, CSRF, honeypot, límite de envíos, precio y disponibilidad recalculados en el servidor, bloqueo de archivo al reservar.
