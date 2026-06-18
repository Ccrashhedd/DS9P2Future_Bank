<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

configurar_api();
requerir_metodo('GET');
usuario_autenticado();

$db = bd(2);
$stmt = $db->query(
    'SELECT idGradoEst, nombreGradoEst
     FROM gradoacademico_documento
     ORDER BY nombreGradoEst ASC'
);

$tipos = array_map(static fn (array $row): array => [
    'idGradoEst' => (int) $row['idGradoEst'],
    'nombreGradoEst' => $row['nombreGradoEst'],
], $stmt->fetchAll());

json_respuesta([
    'ok' => true,
    'data' => [
        'tipos' => $tipos,
    ],
]);
