</main>

<?php $P = $P ?? partido(); $B = $P['boleteria']; ?>
<footer class="footer">
    <div class="wrap footer__grid">
        <div>
            <p class="eyebrow">// <?= e($P['estadio']) ?></p>
            <p class="footer__txt">
                <strong>Dirección de Boletería</strong><br><?= e($B['direccion']) ?><br><br>
                <strong>Horario de Atención</strong><br><?= e($B['horario']) ?>
            </p>
        </div>
        <div>
            <p class="eyebrow">// CONTACTO</p>
            <ul class="footer__list">
                <li><a href="mailto:<?= e($B['correo']) ?>"><?= e($B['correo']) ?></a></li>
                <li><a href="tel:<?= e($B['tel_href']) ?>"><?= e($B['telefono']) ?></a></li>
                <li><a href="<?= e($P['maps']) ?>" target="_blank" rel="noopener noreferrer">Ver ubicación en Google Maps</a></li>
            </ul>
        </div>
        <div>
            <p class="eyebrow">// EL CENTRO</p>
            <ul class="footer__list">
                <li><a href="<?= e(url('sectores.php')) ?>">Sectores y precios</a></li>
                <li><a href="<?= e(url('reservar.php')) ?>">Reservar entradas</a></li>
                <li><a href="<?= e(url('mi-reserva.php')) ?>">Consultar mi reserva</a></li>
                <li><a href="<?= e(url('como-llegar.php')) ?>">Cómo llegar y acceder</a></li>
            </ul>
        </div>
    </div>
    <div class="wrap footer__quote">
        <p>RESERVA EN LÍNEA · RETIRA EN EL CENTRO · CANJEA CON TU CÉDULA Y TU CÓDIGO. SIN FILAS, SIN SORPRESAS.</p>
    </div>
    <div class="wrap footer__legal">
        <span>&copy; <?= date('Y') ?> <?= e($P['estadio']) ?>. Todos los derechos reservados.</span>
        <span>Proyecto de demostración</span>
    </div>
</footer>

<script src="<?= e(asset('js/main.js')) ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
