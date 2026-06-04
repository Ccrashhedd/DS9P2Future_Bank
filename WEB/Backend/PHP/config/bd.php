<?php

declare(strict_types=1);

function bd(int $base): PDO
{
    static $conexiones = [];

    $config = [
        1 => [
            'host' => 'localhost',
            'dbname' => 'ds2gestiongeneral',
            'username' => 'root',
            'password' => '',
        ],
        2 => [
            'host' => 'localhost',
            'dbname' => 'ds2gestiondocumentos',
            'username' => 'root',
            'password' => '',
        ],
    ];

    if (!isset($config[$base])) {
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
        throw new Exception("Error al conectar a la base de datos {$datos['dbname']}: " . $e->getMessage());
    }
}