<?php
require_once __DIR__ . "/../../Backend/PHP/config/sesion.php";
?>

<section class="cta-section section-shell">
    <div class="container cta-card">
        <?php if (isLoggedIn()): ?>
            <div>
                <span class="eyebrow">Comienza ahora</span>
                <h2>Postúlate para unirte a nuestro equipo hoy</h2>
            </div>
            <a href="#/postulacion" class="btn btn-bank-primary" data-link="postulacion">Ir al formulario</a>
        <?php else: ?>
            <div>
                <span class="eyebrow">Crea tu cuenta</span>
                <h2>Inicia sesión o crea tu cuenta para acceder a la postulación</h2>
            </div>
            <a href="#/login" class="btn btn-bank-primary" data-link="login">Iniciar sesión</a>
        <?php endif; ?>
    </div>
</section>
