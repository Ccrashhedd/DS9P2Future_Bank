<?php

declare(strict_types=1);

CONST RUTA_DOCUMENTOS = __DIR__ . "/WEB/ASSETS/DOCUMENTOS/";
function verificarCarpeta() : void {
    if (!is_dir(RUTA_DOCUMENTOS)) {
        mkdir(RUTA_DOCUMENTOS, 0777, true);
    }
}

// Verificar si son archivos pdf
function esArchivoValido(string $nombreArchivo): bool {
    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
    return strtolower($extension) === 'pdf';
}

function guardarDocumento(string $nombreArchivo, string $contenido): bool {
    if (!esArchivoValido($nombreArchivo)) {
        return false;
    }else {
        $rutaCompleta = RUTA_DOCUMENTOS . $nombreArchivo;
        return file_put_contents($rutaCompleta, $contenido) !== false;
    }
    
}


?>