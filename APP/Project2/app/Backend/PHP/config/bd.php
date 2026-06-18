<?php

declare(strict_types=1);

function bd(int $base = 1): PDO
{
    $bases = [
        1 => 'p2gestiongeneral',
        2 => 'p2gestiondocumentos',
    ];

    if (!isset($bases[$base])) {
        throw new InvalidArgumentException('Base de datos no configurada.');
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $puerto = getenv('DB_PORT') ?: '3306';
    $usuario = getenv('DB_USER') ?: 'root';
    $clave = getenv('DB_PASS');
    if ($clave === false) {
        $clave = '';
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $puerto,
        $bases[$base]
    );

    return new PDO($dsn, $usuario, $clave, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
