<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/bd.php';
require_once __DIR__ . '/respuesta.php';

function token_bearer(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($header === '') {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (!$headers && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }

        foreach ($headers as $name => $value) {
            if (strcasecmp((string) $name, 'Authorization') === 0) {
                $header = (string) $value;
                break;
            }
        }
    }

    if (stripos($header, 'Bearer ') === 0) {
        return trim(substr($header, 7));
    }

    return null;
}

function hash_token(string $token): string
{
    return hash('sha256', $token);
}

function asegurar_tabla_sesiones_app(PDO $db): void
{
    static $asegurada = false;
    if ($asegurada) {
        return;
    }

    $db->exec(
        'CREATE TABLE IF NOT EXISTS sesiones_app (
            idSesion BIGINT AUTO_INCREMENT PRIMARY KEY,
            idUsuario BIGINT NOT NULL,
            tokenHash VARCHAR(255) NOT NULL UNIQUE,
            creadoEn DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expiraEn DATETIME NOT NULL,
            revocado TINYINT(1) NOT NULL DEFAULT 0,
            userAgent VARCHAR(255) NULL,
            INDEX idx_sesiones_app_idUsuario (idUsuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $asegurada = true;
}

function crear_sesion(PDO $db, int $idUsuario): array
{
    asegurar_tabla_sesiones_app($db);

    $token = bin2hex(random_bytes(32));
    $hash = hash_token($token);
    $expira = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $stmt = $db->prepare(
        'INSERT INTO sesiones_app (idUsuario, tokenHash, expiraEn, userAgent)
         VALUES (:idUsuario, :tokenHash, :expiraEn, :userAgent)'
    );
    $stmt->execute([
        ':idUsuario' => $idUsuario,
        ':tokenHash' => $hash,
        ':expiraEn' => $expira,
        ':userAgent' => $userAgent,
    ]);

    return [
        'token' => $token,
        'expiraEn' => $expira,
    ];
}

function usuario_autenticado(): array
{
    $token = token_bearer();
    if ($token === null || $token === '') {
        error_respuesta('Token requerido.', 401);
    }

    $db = bd(1);
    asegurar_tabla_sesiones_app($db);

    $stmt = $db->prepare(
        'SELECT u.idUsuario, u.rolUsuario, u.nombreUsuario, u.correo
         FROM sesiones_app s
         INNER JOIN usuarios u ON u.idUsuario = s.idUsuario
         WHERE s.tokenHash = :tokenHash
           AND s.revocado = 0
           AND s.expiraEn > NOW()
         LIMIT 1'
    );
    $stmt->execute([':tokenHash' => hash_token($token)]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        error_respuesta('Token invalido o expirado.', 401);
    }

    return $usuario;
}

function id_postulante_de_usuario(PDO $db, int $idUsuario): ?int
{
    $stmt = $db->prepare(
        'SELECT idPostulante
         FROM postulante
         WHERE idUsuario = :idUsuario
         LIMIT 1'
    );
    $stmt->execute([':idUsuario' => $idUsuario]);
    $row = $stmt->fetch();

    return $row ? (int) $row['idPostulante'] : null;
}
