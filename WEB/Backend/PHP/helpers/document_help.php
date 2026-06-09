<?php

declare(strict_types=1);

CONST RUTA_DOCUMENTOS = __DIR__ . "/WEB/ASSETS/DOCUMENTOS/";
function verificarCarpeta() : void {
    if (!is_dir(RUTA_DOCUMENTOS)) {
        mkdir(RUTA_DOCUMENTOS, 0777, true);
    }
}

function guardarDocumento(string $nombreArchivo, string $contenido): bool {
    $rutaCompleta = RUTA_DOCUMENTOS . $nombreArchivo;
    return file_put_contents($rutaCompleta, $contenido) !== false;
}


?>