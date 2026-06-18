<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('POST');

$data = leer_json();
$nombreUsuario = texto($data['nombreUsuario'] ?? '');
$correo = texto($data['correo'] ?? '');
$password = (string) ($data['password'] ?? '');
$passwordConfirm = (string) ($data['passwordConfirm'] ?? '');

if ($nombreUsuario === '' || $correo === '' || $password === '' || $passwordConfirm === '') {
    error_respuesta('Todos los campos son requeridos.', 422);
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    error_respuesta('Correo invalido.', 422);
}

if ($password !== $passwordConfirm) {
    error_respuesta('Las contrasenas no coinciden.', 422);
}

if (strlen($password) < 6) {
    error_respuesta('La contrasena debe tener al menos 6 caracteres.', 422);
}

$db = bd(1);
$duplicado = $db->prepare(
    'SELECT idUsuario
     FROM usuarios
     WHERE correo = :correo OR nombreUsuario = :nombreUsuario
     LIMIT 1'
);
$duplicado->execute([
    ':correo' => $correo,
    ':nombreUsuario' => $nombreUsuario,
]);

if ($duplicado->fetch()) {
    error_respuesta('El usuario o correo ya existe.', 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare(
    'INSERT INTO usuarios (rolUsuario, nombreUsuario, contrasen, correo)
     VALUES (1, :nombreUsuario, :hash, :correo)'
);
$stmt->execute([
    ':nombreUsuario' => $nombreUsuario,
    ':hash' => $hash,
    ':correo' => $correo,
]);

$idUsuario = (int) $db->lastInsertId();
$sesion = crear_sesion($db, $idUsuario);

json_respuesta([
    'ok' => true,
    'message' => 'Usuario registrado.',
    'data' => [
        'token' => $sesion['token'],
        'expiraEn' => $sesion['expiraEn'],
        'user' => [
            'idUsuario' => $idUsuario,
            'rolUsuario' => 1,
            'nombreUsuario' => $nombreUsuario,
            'correo' => $correo,
        ],
    ],
], 201);
