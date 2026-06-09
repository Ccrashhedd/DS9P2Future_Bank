<?php

include "bd.php";

include "../helpers/password_help.php";

declare (strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$SESSION['user'] = $_SESSION['user'] ?? null;
$SESSION['nombre']= $_SESSION['nombre'] ?? null;


function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function login(String $username, String $password): String {
    $conexion = bd(1);
    $stmt = $conexion->prepare("SELECT id, nombre, password FROM empleados WHERE username = :username");

}

?>