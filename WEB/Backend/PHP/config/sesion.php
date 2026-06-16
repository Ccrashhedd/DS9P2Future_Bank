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

function login(string $userInput, string $password): string
{
    try {
        $conexion = bd(1);

        $stmt = $conexion->prepare("
            SELECT 
                idUsuario,
                nombreUsuario,
                correo,
                contrasen
            FROM empleados
            WHERE nombreUsuario = :input
            OR
            correo = :input
            LIMIT 1
        ");

        $stmt->execute([
            ':input' => $userInput
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "Usuario o contraseña incorrectos";
        }

        if (!verifyPassword($password, $user['contrasen'])) {
            return "Usuario o contraseña incorrectos";
        }

        session_regenerate_id(true);

        $_SESSION['idUser'] = $user['idUsuario'];
        $_SESSION['nombreUser'] = $user['nombreUsuario'];
        $_SESSION['correo'] = $user['correo'];

        return "Login exitoso";

    } catch (PDOException $e) {
        return "Error al iniciar sesión";
    }
}


function registerUser(
    string $nombreUsuario,
    string $correo,
    string $password
): string {
    try {
        $conexion = bd(1);

        $stmt = $conexion->prepare("
            SELECT idUsuario 
            FROM empleados
            WHERE nombreUsuario = :nombreUsuario
            OR
            correo = :correo
            LIMIT 1
        ");

        $stmt->execute([
            ':nombreUsuario' => $nombreUsuario,
            ':correo' => $correo
        ]);

        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            return "El usuario o correo ya está registrado";
        }

        $passwordHash = hashPassword($password);

        $stmt = $conexion->prepare("
            INSERT INTO empleados 
            (nombreUsuario, correo, contrasen)
            VALUES 
            (:nombreUsuario, :correo, :contrasen)
        ");

        $stmt->execute([
            ':nombreUsuario' => $nombreUsuario,
            ':correo' => $correo,
            ':contrasen' => $passwordHash
        ]);

        return "Registro exitoso";

    } catch (PDOException $e) {
        return "Error al registrar usuario";
    }
}


function logout(): void
{
    session_unset();
    session_destroy();
}