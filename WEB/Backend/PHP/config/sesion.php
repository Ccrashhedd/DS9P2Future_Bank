<?php

declare(strict_types=1);

require_once __DIR__ . "/bd.php";
require_once __DIR__ . "/../helpers/password_help.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['idUser']);
}

function currentUserId(): ?int
{
    return isset($_SESSION['idUser']) ? (int) $_SESSION['idUser'] : null;
}

function currentUserRole(): ?int
{
    return isset($_SESSION['rolUsuario']) ? (int) $_SESSION['rolUsuario'] : null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'message' => 'Debes iniciar sesión para continuar.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function login(string $userInput, string $password): string
{
    try {
        $conexion = bd(1);

        $stmt = $conexion->prepare("SELECT idUsuario, 
                        rolUsuario,
                        nombreUsuario,
                        correo,
                        contrasen
                        FROM usuarios
                        WHERE nombreUsuario = :inputNombre
                        OR correo = :inputCorreo
                        LIMIT 1");

        $stmt->execute([
            ':inputNombre' => $userInput,
            ':inputCorreo' => $userInput
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "Usuario o contraseña incorrectos";
        }

        $passwordGuardada = (string) $user['contrasen'];
        $passwordValida = verifyPassword($password, $passwordGuardada);

        // Compatibilidad con usuarios de prueba que vienen en el SQL final con contraseña plana.
        // Si coincide, se actualiza inmediatamente a hash sin cambiar la estructura de la tabla.
        if (!$passwordValida && password_get_info($passwordGuardada)['algo'] === 0 && hash_equals($passwordGuardada, $password)) {
            $passwordValida = true;
            $updateHash = $conexion->prepare('UPDATE usuarios SET contrasen = :hash WHERE idUsuario = :idUsuario');
            $updateHash->execute([
                ':hash' => hashPassword($password),
                ':idUsuario' => (int) $user['idUsuario']
            ]);
        }

        if (!$passwordValida) {
            return "Usuario o contraseña incorrectos";
        }

        session_regenerate_id(true);

        $_SESSION['idUser'] = (int) $user['idUsuario'];
        $_SESSION['rolUsuario'] = (int) $user['rolUsuario'];
        $_SESSION['nombreUser'] = $user['nombreUsuario'];
        $_SESSION['correo'] = $user['correo'];

        return "Login exitoso";
    } catch (PDOException $e) {
        return "Error al iniciar sesión";
    }
}

function registerUser(string $nombreUsuario, string $correo, string $password): string
{
    try {
        $nombreUsuario = trim($nombreUsuario);
        $correo = trim($correo);

        if ($nombreUsuario === '' || $correo === '' || $password === '') {
            return "Todos los campos son obligatorios";
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return "El correo no tiene un formato válido";
        }

        /*
        if (strlen($password) < 8) {
            return "La contraseña debe tener mínimo 8 caracteres";
        }
        */
        

        $conexion = bd(1);

        $stmt = $conexion->prepare("SELECT idUsuario 
                                        FROM usuarios
                                        WHERE nombreUsuario = :nombreUsuario
                                        OR correo = :correo
                                        LIMIT 1");

        $stmt->execute([
            ':nombreUsuario' => $nombreUsuario,
            ':correo' => $correo
        ]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return "El usuario o correo ya está registrado";
        }

        $stmt = $conexion->prepare("INSERT INTO usuarios 
                                        (nombreUsuario, correo, contrasen)
                                        VALUES ( :nombreUsuario, :correo, :contrasen)");

        $stmt->execute([
            ':nombreUsuario' => $nombreUsuario,
            ':correo' => $correo,
            ':contrasen' => hashPassword($password)
        ]);

        return "Registro exitoso";
    } catch (PDOException $e) {
        return "Error al registrar usuario";
    }
}

function logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}
