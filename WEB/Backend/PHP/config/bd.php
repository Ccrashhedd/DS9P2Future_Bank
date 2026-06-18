<?php

declare(strict_types=1);

/**
 * Conexión centralizada a las dos bases de datos del proyecto.
 * Base 1: p2gestiongeneral
 * Base 2: p2gestiondocumentos
 */
function bd(int $base): PDO
{
    static $conexiones = [];

    $config = [
        1 => [
            'host' => 'localhost',
            'dbname' => 'p2gestiongeneral',
            'username' => 'root',
            'password' => '',
        ],
        2 => [
            'host' => 'localhost',
            'dbname' => 'p2gestiondocumentos',
            'username' => 'root',
            'password' => '',
        ],
    ];

    if (!array_key_exists($base, $config)) {
        throw new InvalidArgumentException("Base de datos no válida: {$base}");
    }

    if (isset($conexiones[$base])) {
        return $conexiones[$base];
    }

    $datos = $config[$base];
    $dsn = "mysql:host={$datos['host']};dbname={$datos['dbname']};charset=utf8mb4";

    try {
        $conexiones[$base] = new PDO(
            $dsn,
            $datos['username'],
            $datos['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $conexiones[$base];
    } catch (PDOException $e) {
        throw new RuntimeException("No se pudo conectar a la base {$datos['dbname']}. Revisa bd.php, MySQL y que la base exista.");
    }
}
