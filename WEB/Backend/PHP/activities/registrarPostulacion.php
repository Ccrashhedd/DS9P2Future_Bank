<?php

declare(strict_types=1);

function validarCamposDatosPersonales(array $data): array
{
    $errors = [];

    if (empty($data['nombre'])) {
        $errors[] = "El campo 'nombre' es obligatorio.";
    }

    if (empty($data['apellido'])) {
        $errors[] = "El campo 'apellido' es obligatorio.";
    }

    if (empty($data['correo'])) {
        $errors[] = "El campo 'correo' es obligatorio.";
    } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El campo 'correo' debe ser una dirección de correo electrónico válida.";
    }

    return $errors;
}


function validarCamposUbicacion(array $data): array
{
    $errors = [];

    if (empty($data['comunidad'])) {
        $errors[] = "El campo 'comunidad' es obligatorio.";
    }

    if (empty($data['calle'])) {
        $errors[] = "El campo 'calle' es obligatorio.";
    }

    if (empty($data['casa'])) {
        $errors[] = "El campo 'casa' es obligatorio.";
    }

    return $errors;
}


function validarCamposEducacion(array $data): array
{
    $errors = [];

    if (empty($data['institucion'])) {
        $errors[] = "El campo 'institucion' es obligatorio.";
    }

    if (empty($data['anio_graduacion'])) {
        $errors[] = "El campo 'anio_graduacion' es obligatorio.";
    } elseif (!preg_match('/^\d{4}$/', $data['anio_graduacion'])) {
        $errors[] = "El campo 'anio_graduacion' debe contener exactamente 4 dígitos.";
    }

    return $errors;
}

function validarFechasyHoras(array $data): array
{
    $errors = [];

    if (empty($data['Institucion_educativa'])) {
        $errors[] = "El campo 'Institucion_educativa' es obligatorio.";
    }

    if ($data['Institucion_educativa']== 1 && empty($data['Otra_institucioin'])){
        $errors[] = "Porfavor especifique que otra institucion";
    }

    if (empty($data['anio_finalizacion'])) {
        $errors[] = "El campo 'anio_finalizacion' es obligatorio.";
    } elseif (!preg_match('/^\d{4}$/', (string) $data['anio_finalizacion'])) {
        $errors[] = "El campo 'anio_finalizacion' debe contener exactamente 4 dígitos.";
    }

    return $errors;
}


function validar5anio(string $fecha): bool
{
    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return false;
    }

    $currentYear = (int) date('Y');
    $inputYear = (int) date('Y', $timestamp);

    return $inputYear >= $currentYear - 5 && $inputYear <= $currentYear;
}



?>