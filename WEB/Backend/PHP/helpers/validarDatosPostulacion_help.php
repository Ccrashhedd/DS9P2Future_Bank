<?php

declare(strict_types=1);

function requiredValue(array $data, string $key, string $label, array &$errors): void
{
    if (!isset($data[$key]) || trim((string) $data[$key]) === '') {
        $errors[] = "El campo {$label} es obligatorio.";
    }
}

function validarTelefono(?string $value, string $label, bool $required, array &$errors): void
{
    $value = trim((string) $value);

    if ($value === '') {
        if ($required) {
            $errors[] = "El campo {$label} es obligatorio.";
        }
        return;
    }

    if (!preg_match('/^[0-9]{7,9}$/', $value)) {
        $errors[] = "El campo {$label} debe contener entre 7 y 9 dígitos.";
    }
}

function validarCedulaPartes(array $data, array &$errors): void
{
    foreach (['prefijo' => 'prefijo de cédula', 'tomo' => 'tomo', 'asiento' => 'asiento'] as $key => $label) {
        requiredValue($data, $key, $label, $errors);
    }

    if (!empty($data['tomo']) && !preg_match('/^[1-9][0-9]{0,3}$/', (string) $data['tomo'])) {
        $errors[] = 'El tomo debe tener máximo 4 dígitos y no puede iniciar con 0.';
    }

    if (!empty($data['asiento']) && !preg_match('/^[1-9][0-9]{0,4}$/', (string) $data['asiento'])) {
        $errors[] = 'El asiento debe tener máximo 5 dígitos y no puede iniciar con 0.';
    }
}

function validarPostulante(array $data): array
{
    $errors = [];

    foreach ([
        'rangoAcademico' => 'nivel de estudios',
        'nombre' => 'primer nombre',
        'apellido' => 'primer apellido',
        'genero' => 'sexo',
        'estadoCivil' => 'estado civil',
        'fechaNacimiento' => 'fecha de nacimiento',
        'codigo_provincia' => 'provincia',
        'codigo_distrito' => 'distrito',
        'codigo_corregimiento' => 'corregimiento',
        'comunidad' => 'comunidad',
        'calle' => 'calle',
        'casa' => 'casa',
        'correoPostulante' => 'correo electrónico',
        'celular' => 'celular'
    ] as $key => $label) {
        requiredValue($data, $key, $label, $errors);
    }

    validarCedulaPartes($data, $errors);

    if (!empty($data['correoPostulante']) && !filter_var((string) $data['correoPostulante'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo electrónico no tiene un formato válido.';
    }

    if (isset($data['genero']) && $data['genero'] !== '' && !in_array((string) $data['genero'], ['0', '1'], true)) {
        $errors[] = 'El sexo seleccionado no es válido.';
    }

    if (isset($data['usaCasada']) && $data['usaCasada'] !== '' && !in_array((string) $data['usaCasada'], ['0', '1'], true)) {
        $errors[] = 'La opción de apellido de casada no es válida.';
    }

    $esFemenino = (string) ($data['genero'] ?? '') === '0';
    $estadoCivilPermiteApellidoCasada = in_array((int) ($data['estadoCivil'] ?? 0), [2, 3], true);
    $puedeUsarApellidoCasada = $esFemenino && $estadoCivilPermiteApellidoCasada;
    $usaCasada = (string) ($data['usaCasada'] ?? '0') === '1';

    if (!$puedeUsarApellidoCasada && $usaCasada) {
        $errors[] = 'El apellido de casada solo aplica cuando el sexo es femenino y el estado civil es casada o viuda.';
    }

    if ($puedeUsarApellidoCasada && $usaCasada && trim((string) ($data['apelCasada'] ?? '')) === '') {
        $errors[] = 'Debe indicar el apellido de casada.';
    }

    validarTelefono($data['telefono'] ?? '', 'teléfono', false, $errors);
    validarTelefono($data['telefono2'] ?? '', 'teléfono secundario', false, $errors);
    validarTelefono($data['celular'] ?? '', 'celular', true, $errors);
    validarTelefono($data['celular2'] ?? '', 'celular secundario', false, $errors);

    return $errors;
}

function validarDocumento(array $data, bool $requiereArchivo): array
{
    $errors = [];

    foreach ([
        'titulo' => 'título del documento',
        'idGradoEst' => 'tipo o grado de documento',
        'institucion' => 'institución educativa',
        'codigo_provincia' => 'provincia del documento',
        'fechaInicio' => 'fecha de inicio',
        'fechaFinalizacion' => 'fecha de finalización',
        'fechaEmision' => 'fecha de emisión',
        'totalHoras' => 'total de horas'
    ] as $key => $label) {
        requiredValue($data, $key, $label, $errors);
    }

    if ((int) ($data['institucion'] ?? 0) === 1 && trim((string) ($data['nombreOtraInstitucion'] ?? '')) === '') {
        $errors[] = 'Debe escribir el nombre de la otra institución.';
    }

    $fechaInicio = strtotime((string) ($data['fechaInicio'] ?? ''));
    $fechaFinal = strtotime((string) ($data['fechaFinalizacion'] ?? ''));
    $fechaEmision = strtotime((string) ($data['fechaEmision'] ?? ''));
    $hoy = strtotime(date('Y-m-d'));
    $limite = strtotime('-5 years', $hoy);

    if ($fechaInicio && $fechaFinal && $fechaFinal <= $fechaInicio) {
        $errors[] = 'La fecha de finalización debe ser posterior a la fecha de inicio.';
    }

    if ($fechaFinal && $fechaEmision && $fechaEmision <= $fechaFinal) {
        $errors[] = 'La fecha de emisión debe ser posterior a la fecha de finalización.';
    }

    if ($fechaEmision && ($fechaEmision > $hoy || $fechaEmision < $limite)) {
        $errors[] = 'La fecha de emisión debe estar dentro de los últimos 5 años y no puede ser futura.';
    }

    if (isset($data['totalHoras']) && (int) $data['totalHoras'] < 40) {
        $errors[] = 'El total de horas debe ser mínimo 40.';
    }

    if ($requiereArchivo) {
        $errors[] = 'Debe cargar un archivo PDF para cada documento nuevo.';
    }

    return $errors;
}
