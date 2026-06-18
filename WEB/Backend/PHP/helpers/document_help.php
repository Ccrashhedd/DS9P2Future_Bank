<?php

declare(strict_types=1);

const MAX_DOCUMENT_SIZE = 5242880; // 5 MB
const DOCUMENT_STORAGE_PATH = __DIR__ . '/../../storage/documentos/';

function verificarCarpetaDocumentos(): void
{
    if (!is_dir(DOCUMENT_STORAGE_PATH)) {
        mkdir(DOCUMENT_STORAGE_PATH, 0755, true);
    }

    $htaccess = DOCUMENT_STORAGE_PATH . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Deny from all\n");
    }
}

function esPdfValido(array $file): bool
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > MAX_DOCUMENT_SIZE) {
        return false;
    }

    $nombre = (string) ($file['name'] ?? '');
    if (strtolower(pathinfo($nombre, PATHINFO_EXTENSION)) !== 'pdf') {
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string) $file['tmp_name']);

    return $mime === 'application/pdf' || $mime === 'application/octet-stream';
}

function guardarPdfPostulante(array $file, int $idPostulante): string
{
    verificarCarpetaDocumentos();

    if (!esPdfValido($file)) {
        throw new InvalidArgumentException('El archivo debe ser un PDF válido y no superar los 5 MB.');
    }

    $nombreOriginal = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $file['name']));
    $nombreSeguro = 'postulante_' . $idPostulante . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '_' . $nombreOriginal;
    $rutaDestino = DOCUMENT_STORAGE_PATH . $nombreSeguro;

    if (!move_uploaded_file((string) $file['tmp_name'], $rutaDestino)) {
        throw new RuntimeException('No se pudo guardar el documento PDF.');
    }

    return $nombreSeguro;
}

function rutaFisicaDocumento(string $rutaGuardada): string
{
    return DOCUMENT_STORAGE_PATH . basename($rutaGuardada);
}
