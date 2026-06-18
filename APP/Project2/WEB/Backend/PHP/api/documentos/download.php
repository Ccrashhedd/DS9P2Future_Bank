<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('GET');

function es_ruta_absoluta(string $ruta): bool
{
    return str_starts_with($ruta, '/') || preg_match('/^[A-Za-z]:\\\\/', $ruta) === 1;
}

function resolver_pdf(string $ruta): ?string
{
    $ruta = trim($ruta);
    if ($ruta === '') {
        return null;
    }

    $backendRoot = realpath(__DIR__ . '/../../../');
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== ''
        ? realpath($_SERVER['DOCUMENT_ROOT'])
        : false;

    $candidatas = [];
    if (es_ruta_absoluta($ruta)) {
        $candidatas[] = ['path' => $ruta, 'root' => null];
    } else {
        if ($backendRoot !== false) {
            $candidatas[] = ['path' => $backendRoot . DIRECTORY_SEPARATOR . ltrim($ruta, '/\\'), 'root' => $backendRoot];
        }
        if ($documentRoot !== false) {
            $candidatas[] = ['path' => $documentRoot . DIRECTORY_SEPARATOR . ltrim($ruta, '/\\'), 'root' => $documentRoot];
        }
    }

    foreach ($candidatas as $candidata) {
        $real = realpath($candidata['path']);
        $root = $candidata['root'];
        $dentroDeRoot = $root === null || str_starts_with($real ?: '', $root . DIRECTORY_SEPARATOR);
        if (
            $real !== false
            && $dentroDeRoot
            && is_file($real)
            && strtolower(pathinfo($real, PATHINFO_EXTENSION)) === 'pdf'
        ) {
            return $real;
        }
    }

    return null;
}

$idDocumento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idDocumento) {
    error_respuesta('ID de documento invalido.', 422);
}

$usuario = usuario_autenticado();
$dbGeneral = bd(1);
$idPostulante = id_postulante_de_usuario($dbGeneral, (int) $usuario['idUsuario']);
if ($idPostulante === null) {
    error_respuesta('Documento no encontrado.', 404);
}

$dbDocumentos = bd(2);
$stmt = $dbDocumentos->prepare(
    'SELECT dp.idPostulante, rd.ruta
     FROM documento_postulante dp
     INNER JOIN ruta_documento rd
        ON rd.idDocumentoPostulante = dp.idDocumentoPostulante
     WHERE dp.idDocumentoPostulante = :idDocumento
     LIMIT 1'
);
$stmt->execute([':idDocumento' => $idDocumento]);
$row = $stmt->fetch();

if (!$row) {
    error_respuesta('PDF no encontrado.', 404);
}

if ((int) $row['idPostulante'] !== $idPostulante) {
    error_respuesta('No autorizado para descargar este documento.', 403);
}

$archivo = resolver_pdf((string) $row['ruta']);
if ($archivo === null) {
    error_respuesta('Archivo PDF no disponible.', 404);
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="documento.pdf"');
header('Content-Length: ' . filesize($archivo));
readfile($archivo);
exit;
