<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('GET');

$usuario = usuario_autenticado();
$db = bd(1);

$stmt = $db->prepare(
    'SELECT
        p.idPostulante,
        p.idUsuario,
        p.nombre,
        p.nombre2,
        p.apellido,
        p.apellido2,
        p.prefijo,
        p.tomo,
        p.asiento,
        p.genero,
        p.usaCasada,
        p.apelCasada,
        p.fechaNacimiento,
        p.comunidad,
        p.calle,
        p.casa,
        p.detalleDireccion,
        p.telefono,
        p.telefono2,
        p.celular,
        p.celular2,
        p.correoPostulante,
        ec.nombreEstadoCiv,
        ra.nombreRangoEdu,
        ts.nombreTipoSangre,
        pr.nombre_provincia,
        d.nombre_distrito,
        c.nombre_corregimiento
     FROM postulante p
     INNER JOIN estadocivil ec ON ec.idEstadoCivil = p.estadoCivil
     INNER JOIN rangoacademico ra ON ra.idRangoEdu = p.rangoAcademico
     LEFT JOIN tiposangre ts ON ts.idTipoSangre = p.tipoSangre
     INNER JOIN provincia pr ON pr.codigo_provincia = p.codigo_provincia COLLATE utf8mb4_unicode_ci
     INNER JOIN distrito d ON d.codigo_distrito = p.codigo_distrito COLLATE utf8mb4_unicode_ci
     INNER JOIN corregimiento c ON c.codigo_corregimiento = p.codigo_corregimiento COLLATE utf8mb4_unicode_ci
     WHERE p.idUsuario = :idUsuario
     LIMIT 1'
);
$stmt->execute([':idUsuario' => $usuario['idUsuario']]);
$row = $stmt->fetch();

if (!$row) {
    json_respuesta([
        'ok' => true,
        'message' => 'No hay postulacion registrada',
        'data' => [
            'tienePostulacion' => false,
        ],
    ]);
}

function unir_partes(array $partes, string $separador = ' '): string
{
    $limpias = array_values(array_filter(array_map(
        static fn ($valor): string => trim((string) $valor),
        $partes
    ), static fn (string $valor): bool => $valor !== ''));

    return implode($separador, $limpias);
}

$genero = match ((string) $row['genero']) {
    '1' => 'Masculino',
    '0' => 'Femenino',
    default => 'No especificado',
};

$estadoCivil = (string) $row['nombreEstadoCiv'];
$usaApellidoCasada = (int) ($row['usaCasada'] ?? 0) === 1;
$estadoPermiteApellidoCasada = in_array(strtoupper($estadoCivil), ['CASADO/A', 'VIUDO/A'], true);
$apellidoCasada = null;
if ($genero === 'Femenino' && $estadoPermiteApellidoCasada && $usaApellidoCasada) {
    $apellidoCasada = texto($row['apelCasada'] ?? '');
    if ($apellidoCasada === '') {
        $apellidoCasada = null;
    }
}

$direccion = unir_partes([
    $row['nombre_provincia'],
    $row['nombre_distrito'],
    $row['nombre_corregimiento'],
    $row['comunidad'],
    $row['calle'],
    $row['casa'],
    $row['detalleDireccion'],
], ', ');

json_respuesta([
    'ok' => true,
    'data' => [
        'tienePostulacion' => true,
        'idPostulante' => (int) $row['idPostulante'],
        'nombreCompleto' => unir_partes([$row['nombre'], $row['nombre2'], $row['apellido'], $row['apellido2']]),
        'cedula' => unir_partes([$row['prefijo'], $row['tomo'], $row['asiento']], '-'),
        'genero' => $genero,
        'estadoCivil' => $estadoCivil,
        'apellidoCasada' => $apellidoCasada,
        'rangoAcademico' => $row['nombreRangoEdu'],
        'tipoSangre' => $row['nombreTipoSangre'],
        'fechaNacimiento' => $row['fechaNacimiento'],
        'correoPostulante' => $row['correoPostulante'],
        'telefono' => $row['telefono'],
        'telefono2' => $row['telefono2'],
        'celular' => $row['celular'],
        'celular2' => $row['celular2'],
        'provincia' => $row['nombre_provincia'],
        'distrito' => $row['nombre_distrito'],
        'corregimiento' => $row['nombre_corregimiento'],
        'comunidad' => $row['comunidad'],
        'calle' => $row['calle'],
        'casa' => $row['casa'],
        'detalleDireccion' => $row['detalleDireccion'],
        'direccionCompleta' => $direccion,
    ],
]);
