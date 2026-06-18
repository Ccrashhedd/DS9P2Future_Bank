<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('GET');

$usuario = usuario_autenticado();
$dbGeneral = bd(1);
$idPostulante = id_postulante_de_usuario($dbGeneral, (int) $usuario['idUsuario']);

if ($idPostulante === null) {
    json_respuesta([
        'ok' => true,
        'data' => [
            'documentos' => [],
        ],
    ]);
}

$dbDocumentos = bd(2);
$where = ['dp.idPostulante = :idPostulante'];
$params = [':idPostulante' => $idPostulante];
$search = texto($_GET['search'] ?? '');
$tipo = texto($_GET['tipo'] ?? '');

if ($search !== '') {
    $where[] = 'dp.titulo LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

if ($tipo !== '') {
    if (ctype_digit($tipo)) {
        $where[] = 'dp.idGradoEst = :tipoId';
        $params[':tipoId'] = (int) $tipo;
    } else {
        $where[] = 'gad.nombreGradoEst = :tipoNombre';
        $params[':tipoNombre'] = $tipo;
    }
}

$sql = 'SELECT
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
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY dp.fechaEmision DESC, dp.idDocumentoPostulante DESC';

$stmt = $dbDocumentos->prepare($sql);
$stmt->execute($params);
$documentos = [];

foreach ($stmt->fetchAll() as $row) {
    $institucion = ((int) ($row['otraInstitucion'] ?? 0) === 1)
        ? $row['nombreOtraInstitucion']
        : $row['nombreInstitucion'];

    $documentos[] = [
        'idDocumentoPostulante' => (int) $row['idDocumentoPostulante'],
        'titulo' => $row['titulo'],
        'tipo' => $row['nombreGradoEst'],
        'institucion' => $institucion,
        'fechaInicio' => $row['fechaInicio'],
        'fechaFinalizacion' => $row['fechaFinalizacion'],
        'fechaEmision' => $row['fechaEmision'],
        'totalHoras' => $row['totalHoras'] === null ? null : (int) $row['totalHoras'],
        'tienePdf' => $row['idRutadoc'] !== null && texto($row['ruta']) !== '',
    ];
}

json_respuesta([
    'ok' => true,
    'data' => [
        'documentos' => $documentos,
    ],
]);
