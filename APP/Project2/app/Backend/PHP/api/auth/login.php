<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('POST');

$data = leer_json();
$usuarioLogin = texto($data['usuario'] ?? $data['correo'] ?? $data['nombreUsuario'] ?? '');
$password = (string) ($data['password'] ?? '');

if ($usuarioLogin === '' || $password === '') {
    error_respuesta('Usuario y password son requeridos.', 422);
}

$db = bd(1);
$stmt = $db->prepare(
    'SELECT idUsuario, rolUsuario, nombreUsuario, contrasen, correo
     FROM usuarios
     WHERE correo = :correo OR nombreUsuario = :nombreUsuario
     LIMIT 1'
);
$stmt->execute([
    ':correo' => $usuarioLogin,
    ':nombreUsuario' => $usuarioLogin,
]);
$usuario = $stmt->fetch();

if (!$usuario) {
    error_respuesta('Credenciales invalidas.', 401);
}

$hashActual = (string) $usuario['contrasen'];
$valido = password_verify($password, $hashActual);

if (!$valido && hash_equals($hashActual, $password)) {
    $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
    $update = $db->prepare(
        'UPDATE usuarios
         SET contrasen = :hash
         WHERE idUsuario = :idUsuario'
    );
    $update->execute([
        ':hash' => $nuevoHash,
        ':idUsuario' => $usuario['idUsuario'],
    ]);
    $valido = true;
}

if (!$valido) {
    error_respuesta('Credenciales invalidas.', 401);
}

$sesion = crear_sesion($db, (int) $usuario['idUsuario']);

json_respuesta([
    'ok' => true,
    'message' => 'Login exitoso.',
    'data' => [
        'token' => $sesion['token'],
        'expiraEn' => $sesion['expiraEn'],
        'user' => [
            'idUsuario' => (int) $usuario['idUsuario'],
            'rolUsuario' => (int) $usuario['rolUsuario'],
            'nombreUsuario' => $usuario['nombreUsuario'],
            'correo' => $usuario['correo'],
        ],
    ],
]);
