<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bd.php';

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../activities/PostulacionService.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'catalogos') {
        // Los catálogos no contienen información sensible. Se dejan públicos para que
        // los select se carguen dinámicamente aunque el formulario se abra antes de iniciar sesión.
        jsonResponse(true, 'Catálogos cargados.', ['data' => obtenerCatalogos()]);
    }

    if ($action === 'mi_postulacion') {
        requireLogin();
        jsonResponse(true, 'Postulación cargada.', ['data' => obtenerMiPostulacion((int) currentUserId())]);
    }

    if ($action === 'guardar_postulacion') {
        requireLogin();
        $resultado = guardarPostulacionCompleta((int) currentUserId(), $_POST);
        jsonResponse(
            (bool) $resultado['ok'],
            (string) $resultado['message'],
            ['errors' => $resultado['errors'] ?? []],
            $resultado['ok'] ? 200 : 422
        );
    }

    if ($action === 'descargar_documento') {
        requireLogin();
        $idDocumento = (int) ($_GET['id'] ?? 0);
        if ($idDocumento <= 0) {
            http_response_code(400);
            exit('Documento inválido.');
        }
        descargarDocumentoSeguro((int) currentUserId(), $idDocumento);
    }

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $resultado = login($username, $password);

        if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            jsonResponse($resultado === 'Login exitoso', $resultado, [], $resultado === 'Login exitoso' ? 200 : 401);
        }

        if ($resultado === 'Login exitoso') {
            header('Location: ../../../Frontend/index.php#/home');
            exit;
        }

        header('Location: ../../../Frontend/index.php#/login?error=' . urlencode($resultado));
        exit;
    }

    if ($action === 'register') {
        $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $resultado = registerUser($nombreUsuario, $correo, $password);

        if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            jsonResponse($resultado === 'Registro exitoso', $resultado, [], $resultado === 'Registro exitoso' ? 200 : 422);
        }

        if ($resultado === 'Registro exitoso') {
            header('Location: ../../../Frontend/index.php#/login?success=' . urlencode($resultado));
            exit;
        }

        header('Location: ../../../Frontend/index.php#/login?error=' . urlencode($resultado));
        exit;
    }

    if ($action === 'logout') {
        logout();
        header('Location: ../../../Frontend/index.php#/login');
        exit;
    }

    jsonResponse(false, 'Acción no válida.', [], 400);
} catch (Throwable $e) {
    jsonResponse(false, 'Ocurrió un error inesperado.', ['errors' => [$e->getMessage()]], 500);
}
