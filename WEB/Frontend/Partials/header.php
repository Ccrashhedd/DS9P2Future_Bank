<?php
require_once __DIR__ . '/../../Backend/PHP/config/sesion.php';
?>

<header class="site-header">
    <nav class="navbar navbar-expand-lg bank-navbar" aria-label="Navegación principal">
        <div class="container">
            <a class="navbar-brand brand" href="#/home" data-link="home" aria-label="Future Bank inicio">
                <span class="brand-mark">FB</span>
                <span class="brand-text">
                    <strong>Future Bank</strong>
                    <small>Banca inteligente</small>
                </span>
            </a>

            <button class="navbar-toggler bank-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <?php if(isLoggedIn()): ?>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <div>
                        <span class="navbar-text me-3 d-none d-lg-block">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombreUser'] ?? 'Usuario'); ?></span>
                    </div>
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link" href="#/home" data-link="home">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#/postulacion" data-link="postulacion">Postulación</a></li>
                        <li class="nav-item"><a class="nav-link" href="#/postulacionusuario" data-link="postulacionusuario">Mi postulación</a></li>
                        <li class="nav-item"><a class="nav-link nav-pill" href="#/logout" data-link="logout">Cerrar sesión</a></li>
                    </ul>
                </div>

            <?php else: ?>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link" href="#/home" data-link="home">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#/postulacion" data-link="postulacion">Postulación</a></li>
                        <li class="nav-item"><a class="nav-link" href="#/postulacionusuario" data-link="postulacionusuario">Mi postulación</a></li>
                        <li class="nav-item"><a class="nav-link nav-pill" href="#/login" data-link="login">Acceso</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            
        </div>
    </nav>
</header>
