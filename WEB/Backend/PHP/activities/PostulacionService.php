<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../helpers/document_help.php';
require_once __DIR__ . '/../helpers/validarDatosPostulacion_help.php';

function jsonResponse(bool $ok, string $message, array $data = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'ok' => $ok,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function nullIfEmpty(?string $value): ?string
{
    $value = trim((string) $value);
    return $value === '' ? null : $value;
}

function obtenerInstitucionesCatalogo(PDO $documental): array
{
    try {
        return $documental->query('SELECT MIN(idInstitucion) AS idInstitucion, nombreInstitucion FROM instituciones GROUP BY nombreInstitucion ORDER BY CASE WHEN MIN(idInstitucion) = 1 THEN 0 ELSE 1 END, nombreInstitucion ASC')->fetchAll();
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02') {
            throw $e;
        }

        return [
            [
                'idInstitucion' => 1,
                'nombreInstitucion' => 'Otra institucion',
            ],
        ];
    }
}

function obtenerCatalogos(): array
{
    $general = bd(1);
    $documental = bd(2);
    $provinciasCedula = $general->query("SELECT codigo_provincia AS prefijo_cedula, codigo_provincia AS etiqueta_prefijo_cedula FROM provincia ORDER BY CAST(codigo_provincia AS UNSIGNED), codigo_provincia")->fetchAll();

    return [
        'provincias' => $general->query('SELECT codigo_provincia, nombre_provincia FROM provincia ORDER BY nombre_provincia ')->fetchAll(),
        'provinciasCedula' => $provinciasCedula,
        'provincias_cedulas' => $provinciasCedula,
        'distritos' => $general->query('SELECT codigo_provincia, codigo_distrito, nombre_distrito FROM distrito ORDER BY nombre_distrito')->fetchAll(),
        'corregimientos' => $general->query('SELECT codigo_distrito, codigo_corregimiento, nombre_corregimiento FROM corregimiento ORDER BY nombre_corregimiento')->fetchAll(),
        'estadosCiviles' => $general->query('SELECT idEstadoCivil, nombreEstadoCiv FROM estadocivil ORDER BY idEstadoCivil')->fetchAll(),
        'rangosAcademicos' => $general->query('SELECT idRangoEdu, nombreRangoEdu FROM rangoacademico ORDER BY idRangoEdu')->fetchAll(),
        'tiposSangre' => $general->query('SELECT idTipoSangre, nombreTipoSangre FROM tiposangre ORDER BY idTipoSangre ')->fetchAll(),
        'gradosDocumento' => $documental->query('SELECT idGradoEst, nombreGradoEst FROM gradoacademico_documento ORDER BY idGradoEst ')->fetchAll(),
        'instituciones' => obtenerInstitucionesCatalogo($documental),
    
    ];
}

function obtenerMiPostulacion(int $idUsuario): array
{
    $general = bd(1);
    $documental = bd(2);

    $stmt = $general->prepare('SELECT * FROM postulante WHERE idUsuario = :idUsuario LIMIT 1');
    $stmt->execute([':idUsuario' => $idUsuario]);
    $postulante = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $documentos = [];

    if ($postulante) {
        $stmtDocs = $documental->prepare("\n            SELECT\n                dp.*,\n                gd.nombreGradoEst,\n                ins.nombreInstitucion,\n                rd.ruta\n            FROM documento_postulante dp\n            INNER JOIN gradoacademico_documento gd ON gd.idGradoEst = dp.idGradoEst\n            INNER JOIN instituciones ins ON ins.idInstitucion = dp.institucion\n            LEFT JOIN ruta_documento rd ON rd.idDocumentoPostulante = dp.idDocumentoPostulante\n            WHERE dp.idPostulante = :idPostulante\n            ORDER BY dp.idDocumentoPostulante ASC\n        ");
        $stmtDocs->execute([':idPostulante' => (int) $postulante['idPostulante']]);
        $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        'postulante' => $postulante,
        'documentos' => $documentos
    ];
}

function normalizarDocumentoPost(array $documento): array
{
    return [
        'idDocumentoPostulante' => isset($documento['idDocumentoPostulante']) && $documento['idDocumentoPostulante'] !== '' ? (int) $documento['idDocumentoPostulante'] : null,
        'titulo' => trim((string) ($documento['tituloDocumento'] ?? $documento['titulo'] ?? '')),
        'idGradoEst' => (int) ($documento['slctGradoEstudio'] ?? $documento['idGradoEst'] ?? 0),
        'institucion' => (int) ($documento['slctInstitucionEducativa'] ?? $documento['institucion'] ?? 0),
        'codigo_provincia' => trim((string) ($documento['provinciaDocumento'] ?? $documento['codigo_provincia'] ?? '')),
        'nombreOtraInstitucion' => nullIfEmpty((string) ($documento['nombreOtraInstitucion'] ?? $documento['otraInstitucion'] ?? '')),
        'fechaInicio' => trim((string) ($documento['fechaInicioEstudios'] ?? $documento['fechaInicio'] ?? '')),
        'fechaFinalizacion' => trim((string) ($documento['fechaFinEstudios'] ?? $documento['fechaFinalizacion'] ?? '')),
        'fechaEmision' => trim((string) ($documento['fechaEmision'] ?? '')),
        'totalHoras' => (int) ($documento['horasTotales'] ?? $documento['totalHoras'] ?? 0),
    ];
}

function normalizarPostulantePost(array $post): array
{
    $genero = (string) ($post['sexo'] ?? $post['genero'] ?? '');
    $usaCasada = (int) ($post['preguntaApelCasada'] ?? $post['usaCasada'] ?? 0);
    $apelCasada = nullIfEmpty((string) ($post['apellidoCasada'] ?? $post['apelCasada'] ?? ''));

    if ($genero !== '0') {
        $usaCasada = 0;
        $apelCasada = null;
    }

    return [
        'rangoAcademico' => (int) ($post['slctNivelEstudios'] ?? $post['rangoAcademico'] ?? 0),
        'nombre' => trim((string) ($post['nombre'] ?? '')),
        'nombre2' => nullIfEmpty((string) ($post['nombre2'] ?? '')),
        'apellido' => trim((string) ($post['apellido'] ?? '')),
        'apellido2' => nullIfEmpty((string) ($post['apellido2'] ?? '')),
        'prefijo' => trim((string) ($post['slctprovinciaCedula'] ?? $post['prefijo'] ?? '')),
        'tomo' => trim((string) ($post['tomo'] ?? '')),
        'asiento' => trim((string) ($post['asiento'] ?? '')),
        'genero' => $genero,
        'estadoCivil' => (int) ($post['slctEstadoCivil'] ?? $post['estadoCivil'] ?? 0),
        'usaCasada' => $usaCasada,
        'apelCasada' => $apelCasada,
        'tipoSangre' => isset($post['slctTipoSangre']) && $post['slctTipoSangre'] !== '' ? (int) $post['slctTipoSangre'] : null,
        'fechaNacimiento' => trim((string) ($post['fechaNacimiento'] ?? '')),
        'codigo_provincia' => trim((string) ($post['slctProvincia'] ?? $post['codigo_provincia'] ?? '')),
        'codigo_distrito' => trim((string) ($post['slctDistrito'] ?? $post['codigo_distrito'] ?? '')),
        'codigo_corregimiento' => trim((string) ($post['slctCorregimiento'] ?? $post['codigo_corregimiento'] ?? '')),
        'comunidad' => trim((string) ($post['comunidad'] ?? '')),
        'calle' => trim((string) ($post['calle'] ?? '')),
        'casa' => trim((string) ($post['casa'] ?? '')),
        'detalleDireccion' => nullIfEmpty((string) ($post['detalleUbicacion'] ?? $post['detalleDireccion'] ?? '')),
        'telefono' => nullIfEmpty((string) ($post['telefono'] ?? '')),
        'telefono2' => nullIfEmpty((string) ($post['telefono2'] ?? '')),
        'celular' => trim((string) ($post['celular'] ?? '')),
        'celular2' => nullIfEmpty((string) ($post['celular2'] ?? '')),
        'correoPostulante' => trim((string) ($post['correoElectronico'] ?? $post['correoPostulante'] ?? '')),
    ];
}

function obtenerArchivoDocumento(int $index): ?array
{
    if (!isset($_FILES['documentos'])) {
        return null;
    }

    $base = $_FILES['documentos'];

    if (!isset($base['name'][$index]['archivoDocumento'])) {
        return null;
    }

    return [
        'name' => $base['name'][$index]['archivoDocumento'],
        'type' => $base['type'][$index]['archivoDocumento'] ?? '',
        'tmp_name' => $base['tmp_name'][$index]['archivoDocumento'] ?? '',
        'error' => $base['error'][$index]['archivoDocumento'] ?? UPLOAD_ERR_NO_FILE,
        'size' => $base['size'][$index]['archivoDocumento'] ?? 0,
    ];
}

function existeDocumentoPostulante(PDO $documental, int $idDocumento, int $idPostulante): bool
{
    $stmt = $documental->prepare('SELECT idDocumentoPostulante FROM documento_postulante WHERE idDocumentoPostulante = :id AND idPostulante = :idPostulante LIMIT 1');
    $stmt->execute([
        ':id' => $idDocumento,
        ':idPostulante' => $idPostulante
    ]);
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

function eliminarDocumentoPostulante(PDO $documental, int $idDocumento, int $idPostulante): void
{
    if (!existeDocumentoPostulante($documental, $idDocumento, $idPostulante)) {
        return;
    }

    $stmt = $documental->prepare('SELECT ruta FROM ruta_documento WHERE idDocumentoPostulante = :idDocumento LIMIT 1');
    $stmt->execute([':idDocumento' => $idDocumento]);
    $ruta = $stmt->fetchColumn();

    // Primero se elimina la ruta porque ruta_documento tiene FK hacia documento_postulante.
    $documental->prepare('DELETE FROM ruta_documento WHERE idDocumentoPostulante = :idDocumento')
        ->execute([':idDocumento' => $idDocumento]);

    $documental->prepare('DELETE FROM documento_postulante WHERE idDocumentoPostulante = :idDocumento AND idPostulante = :idPostulante')
        ->execute([
            ':idDocumento' => $idDocumento,
            ':idPostulante' => $idPostulante
        ]);

    if ($ruta) {
        $fisica = rutaFisicaDocumento((string) $ruta);
        if (is_file($fisica)) {
            unlink($fisica);
        }
    }
}

function upsertPostulante(PDO $general, int $idUsuario, array $data): int
{
    $stmt = $general->prepare('SELECT idPostulante FROM postulante WHERE idUsuario = :idUsuario LIMIT 1');
    $stmt->execute([':idUsuario' => $idUsuario]);
    $idPostulante = $stmt->fetchColumn();

    // Evita que dos postulantes compartan la misma cédula lógica.
    $stmtCedula = $general->prepare("
        SELECT idPostulante
        FROM postulante
        WHERE prefijo = :prefijo
          AND tomo = :tomo
          AND asiento = :asiento
          AND idUsuario <> :idUsuario
        LIMIT 1
    ");
    $stmtCedula->execute([
        ':prefijo' => $data['prefijo'],
        ':tomo' => $data['tomo'],
        ':asiento' => $data['asiento'],
        ':idUsuario' => $idUsuario
    ]);

    if ($stmtCedula->fetchColumn()) {
        throw new InvalidArgumentException('Ya existe una postulación registrada con esa cédula.');
    }

    $params = [
        ':idUsuario' => $idUsuario,
        ':rangoAcademico' => $data['rangoAcademico'],
        ':nombre' => $data['nombre'],
        ':nombre2' => $data['nombre2'],
        ':apellido' => $data['apellido'],
        ':apellido2' => $data['apellido2'],
        ':prefijo' => $data['prefijo'],
        ':tomo' => $data['tomo'],
        ':asiento' => $data['asiento'],
        ':genero' => (int) $data['genero'],
        ':estadoCivil' => $data['estadoCivil'],
        ':usaCasada' => $data['usaCasada'],
        ':apelCasada' => $data['apelCasada'],
        ':tipoSangre' => $data['tipoSangre'],
        ':fechaNacimiento' => $data['fechaNacimiento'],
        ':codigo_provincia' => $data['codigo_provincia'],
        ':codigo_distrito' => $data['codigo_distrito'],
        ':codigo_corregimiento' => $data['codigo_corregimiento'],
        ':comunidad' => $data['comunidad'],
        ':calle' => $data['calle'],
        ':casa' => $data['casa'],
        ':detalleDireccion' => $data['detalleDireccion'],
        ':telefono' => $data['telefono'],
        ':telefono2' => $data['telefono2'],
        ':celular' => $data['celular'],
        ':celular2' => $data['celular2'],
        ':correoPostulante' => $data['correoPostulante'],
    ];

    if ($idPostulante) {
        $params[':idPostulante'] = (int) $idPostulante;
        $sql = "\n            UPDATE postulante SET\n                rangoAcademico = :rangoAcademico,\n                nombre = :nombre,\n                nombre2 = :nombre2,\n                apellido = :apellido,\n                apellido2 = :apellido2,\n                prefijo = :prefijo,\n                tomo = :tomo,\n                asiento = :asiento,\n                genero = :genero,\n                estadoCivil = :estadoCivil,\n                usaCasada = :usaCasada,\n                apelCasada = :apelCasada,\n                tipoSangre = :tipoSangre,\n                fechaNacimiento = :fechaNacimiento,\n                codigo_provincia = :codigo_provincia,\n                codigo_distrito = :codigo_distrito,\n                codigo_corregimiento = :codigo_corregimiento,\n                comunidad = :comunidad,\n                calle = :calle,\n                casa = :casa,\n                detalleDireccion = :detalleDireccion,\n                telefono = :telefono,\n                telefono2 = :telefono2,\n                celular = :celular,\n                celular2 = :celular2,\n                correoPostulante = :correoPostulante\n            WHERE idPostulante = :idPostulante\n              AND idUsuario = :idUsuario\n        ";
        $general->prepare($sql)->execute($params);
        return (int) $idPostulante;
    }

    $sql = "\n        INSERT INTO postulante (\n            idUsuario, rangoAcademico, nombre, nombre2, apellido, apellido2,\n            prefijo, tomo, asiento, genero, estadoCivil, usaCasada, apelCasada, tipoSangre, fechaNacimiento,\n            codigo_provincia, codigo_distrito, codigo_corregimiento, comunidad, calle, casa, detalleDireccion,\n            telefono, telefono2, celular, celular2, correoPostulante\n        ) VALUES (\n            :idUsuario, :rangoAcademico, :nombre, :nombre2, :apellido, :apellido2,\n            :prefijo, :tomo, :asiento, :genero, :estadoCivil, :usaCasada, :apelCasada, :tipoSangre, :fechaNacimiento,\n            :codigo_provincia, :codigo_distrito, :codigo_corregimiento, :comunidad, :calle, :casa, :detalleDireccion,\n            :telefono, :telefono2, :celular, :celular2, :correoPostulante\n        )\n    ";

    $general->prepare($sql)->execute($params);
    return (int) $general->lastInsertId();
}

function upsertDocumento(PDO $documental, int $idPostulante, array $data, ?array $file): int
{
    $idDocumento = $data['idDocumentoPostulante'];
    $otraInstitucion = ((int) $data['institucion'] === 1) ? 1 : 0;
    $nombreOtra = $otraInstitucion === 1 ? $data['nombreOtraInstitucion'] : null;

    $params = [
        ':idGradoEst' => $data['idGradoEst'],
        ':idPostulante' => $idPostulante,
        ':codigo_provincia' => $data['codigo_provincia'],
        ':titulo' => $data['titulo'],
        ':institucion' => $data['institucion'],
        ':otraInstitucion' => $otraInstitucion,
        ':nombreOtraInstitucion' => $nombreOtra,
        ':fechaInicio' => $data['fechaInicio'],
        ':fechaFinalizacion' => $data['fechaFinalizacion'],
        ':fechaEmision' => $data['fechaEmision'],
        ':totalHoras' => $data['totalHoras'],
    ];

    if ($idDocumento && existeDocumentoPostulante($documental, (int) $idDocumento, $idPostulante)) {
        $params[':idDocumentoPostulante'] = (int) $idDocumento;
        $documental->prepare("\n            UPDATE documento_postulante SET\n                idGradoEst = :idGradoEst,\n                codigo_provincia = :codigo_provincia,\n                titulo = :titulo,\n                institucion = :institucion,\n                otraInstitucion = :otraInstitucion,\n                nombreOtraInstitucion = :nombreOtraInstitucion,\n                fechaInicio = :fechaInicio,\n                fechaFinalizacion = :fechaFinalizacion,\n                fechaEmision = :fechaEmision,\n                totalHoras = :totalHoras\n            WHERE idDocumentoPostulante = :idDocumentoPostulante\n              AND idPostulante = :idPostulante\n        ")->execute($params);
    } else {
        $documental->prepare("\n            INSERT INTO documento_postulante (\n                idGradoEst, idPostulante, codigo_provincia, titulo, institucion, otraInstitucion,\n                nombreOtraInstitucion, fechaInicio, fechaFinalizacion, fechaEmision, totalHoras\n            ) VALUES (\n                :idGradoEst, :idPostulante, :codigo_provincia, :titulo, :institucion, :otraInstitucion,\n                :nombreOtraInstitucion, :fechaInicio, :fechaFinalizacion, :fechaEmision, :totalHoras\n            )\n        ")->execute($params);
        $idDocumento = (int) $documental->lastInsertId();
    }

    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $rutaNueva = guardarPdfPostulante($file, $idPostulante);

        $stmt = $documental->prepare('SELECT ruta FROM ruta_documento WHERE idDocumentoPostulante = :id LIMIT 1');
        $stmt->execute([':id' => $idDocumento]);
        $rutaAnterior = $stmt->fetchColumn();
        if ($rutaAnterior) {
            $documental->prepare("
                UPDATE ruta_documento
                SET ruta = :ruta
                WHERE idDocumentoPostulante = :idDocumentoPostulante
            ")->execute([
                ':idDocumentoPostulante' => $idDocumento,
                ':ruta' => $rutaNueva
            ]);
        } else {
            $documental->prepare("
                INSERT INTO ruta_documento (idDocumentoPostulante, ruta)
                VALUES (:idDocumentoPostulante, :ruta)
            ")->execute([
                ':idDocumentoPostulante' => $idDocumento,
                ':ruta' => $rutaNueva
            ]);
        }

        if ($rutaAnterior) {
            $fisica = rutaFisicaDocumento((string) $rutaAnterior);
            if (is_file($fisica)) {
                unlink($fisica);
            }
        }
    }

    return (int) $idDocumento;
}

function guardarPostulacionCompleta(int $idUsuario, array $post): array
{
    $general = bd(1);
    $documental = bd(2);

    $postulanteData = normalizarPostulantePost($post);
    $errores = validarPostulante($postulanteData);

    $documentosPost = isset($post['documentos']) && is_array($post['documentos']) ? $post['documentos'] : [];

    if (count($documentosPost) === 0) {
        $errores[] = 'Debe agregar al menos un documento académico.';
    }

    $documentos = [];
    foreach ($documentosPost as $index => $documentoPost) {
        if (!is_array($documentoPost)) {
            continue;
        }

        $doc = normalizarDocumentoPost($documentoPost);
        $file = obtenerArchivoDocumento((int) $index);
        $requiereArchivo = empty($doc['idDocumentoPostulante']) && (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE);
        $docErrors = validarDocumento($doc, $requiereArchivo);

        foreach ($docErrors as $error) {
            $errores[] = 'Documento #' . ((int) $index + 1) . ': ' . $error;
        }

        $documentos[] = [
            'data' => $doc,
            'file' => $file
        ];
    }

    if ($errores) {
        return [
            'ok' => false,
            'message' => 'Hay errores en el formulario.',
            'errors' => $errores
        ];
    }

    try {
        $general->beginTransaction();
        $documental->beginTransaction();

        $idPostulante = upsertPostulante($general, $idUsuario, $postulanteData);

        $eliminados = isset($post['documentosEliminados']) && is_array($post['documentosEliminados']) ? $post['documentosEliminados'] : [];
        foreach ($eliminados as $idEliminar) {
            eliminarDocumentoPostulante($documental, (int) $idEliminar, $idPostulante);
        }

        foreach ($documentos as $documento) {
            upsertDocumento($documental, $idPostulante, $documento['data'], $documento['file']);
        }

        $documental->commit();
        $general->commit();

        return [
            'ok' => true,
            'message' => 'Postulación guardada correctamente.'
        ];
    } catch (Throwable $e) {
        if ($documental->inTransaction()) {
            $documental->rollBack();
        }
        if ($general->inTransaction()) {
            $general->rollBack();
        }

        return [
            'ok' => false,
            'message' => 'No se pudo guardar la postulación.',
            'errors' => [$e->getMessage()]
        ];
    }
}

function descargarDocumentoSeguro(int $idUsuario, int $idDocumento): void
{
    $general = bd(1);
    $documental = bd(2);

    $stmtPostulante = $general->prepare('SELECT idPostulante FROM postulante WHERE idUsuario = :idUsuario LIMIT 1');
    $stmtPostulante->execute([':idUsuario' => $idUsuario]);
    $idPostulante = $stmtPostulante->fetchColumn();

    if (!$idPostulante) {
        http_response_code(404);
        exit('No se encontró la postulación.');
    }

    $stmt = $documental->prepare("\n        SELECT dp.titulo, rd.ruta\n        FROM documento_postulante dp\n        INNER JOIN ruta_documento rd ON rd.idDocumentoPostulante = dp.idDocumentoPostulante\n        WHERE dp.idDocumentoPostulante = :idDocumento\n          AND dp.idPostulante = :idPostulante\n        LIMIT 1\n    ");
    $stmt->execute([
        ':idDocumento' => $idDocumento,
        ':idPostulante' => (int) $idPostulante
    ]);

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$documento) {
        http_response_code(404);
        exit('Documento no encontrado.');
    }

    $ruta = rutaFisicaDocumento((string) $documento['ruta']);
    if (!is_file($ruta)) {
        http_response_code(404);
        exit('Archivo no encontrado.');
    }

    $nombreDescarga = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) $documento['titulo']) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
    exit;
}
