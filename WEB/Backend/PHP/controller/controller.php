<?php

declare(strict_types=1);

require_once __DIR__ . "/../services/auth_service.php";

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $resultado = login($username, $password);

    if ($resultado === "Login exitoso") {
        header("Location: ../../Frontend/index.html#/home");
        exit;
    }

    header("Location: ../../Frontend/index.html#/login?error=" . urlencode($resultado));
    exit;
}

if ($action === 'register') {
    $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    $resultado = registerUser($nombreUsuario, $username, $correo, $password);

    if ($resultado === "Registro exitoso") {
        header("Location: ../../Frontend/index.html#/login?success=" . urlencode($resultado));
        exit;
    }

    header("Location: ../../Frontend/index.html#/register?error=" . urlencode($resultado));
    exit;
}

if ($action === 'logout') {
    logout();
    header("Location: ../../Frontend/index.html#/login");
    exit;
}