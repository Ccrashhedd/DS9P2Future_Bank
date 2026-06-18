<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('GET');

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
    'SELECT
        dp.idDocumentoPostulante,
        dp.idGradoEst,
        gad.nombreGradoEst,
        dp.idPostulante,
        dp.codigo_provincia,
        dp.titulo,
        dp.institucion,
        i.nombreInstitucion,
        dp.otraInstitucion,
        dp.nombreOtraInstitucion,
        dp.fechaInicio,
        dp.fechaFinalizacion,
        dp.fechaEmision,
        dp.totalHoras,
        rd.idRutadoc,
        rd.ruta
     FROM documento_postulante dp
     INNER JOIN gradoacademico_documento gad
        ON gad.idGradoEst = dp.idGradoEst
     LEFT JOIN instituciones i
        ON i.idInstitucion = dp.institucion
     LEFT JOIN ruta_documento rd
        ON rd.idDocumentoPostulante = dp.idDocumentoPostulante
     WHERE dp.idDocumentoPostulante = :idDocumento
     LIMIT 1'
);
$stmt->execute([':idDocumento' => $idDocumento]);
$row = $stmt->fetch();

if (!$row) {
    error_respuesta('Documento no encontrado.', 404);
}

if ((int) $row['idPostulante'] !== $idPostulante) {
    error_respuesta('No autorizado para ver este documento.', 403);
}

$institucion = ((int) ($row['otraInstitucion'] ?? 0) === 1)
    ? $row['nombreOtraInstitucion']
    : $row['nombreInstitucion'];

json_respuesta([
    'ok' => true,
    'data' => [
        'idDocumentoPostulante' => (int) $row['idDocumentoPostulante'],
        'idGradoEst' => (int) $row['idGradoEst'],
        'titulo' => $row['titulo'],
        'tipo' => $row['nombreGradoEst'],
        'institucion' => $institucion,
        'fechaInicio' => $row['fechaInicio'],
        'fechaFinalizacion' => $row['fechaFinalizacion'],
        'fechaEmision' => $row['fechaEmision'],
        'totalHoras' => $row['totalHoras'] === null ? null : (int) $row['totalHoras'],
        'tienePdf' => $row['idRutadoc'] !== null && texto($row['ruta']) !== '',
    ],
]);
