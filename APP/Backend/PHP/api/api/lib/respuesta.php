<?php

declare(strict_types=1);

function configurar_api(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_respuesta(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error_respuesta(string $message, int $status = 400, array $extra = []): never
{
    json_respuesta(array_merge([
        'ok' => false,
        'message' => $message,
    ], $extra), $status);
}

function leer_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST ?: [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_respuesta('JSON invalido.', 400);
    }

    return $data;
}

function requerir_metodo(string $metodo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
        error_respuesta('Metodo no permitido.', 405);
    }
}

function texto(?string $valor): string
{
    return trim((string) $valor);
}
