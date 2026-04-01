<?php
/**
 * Script para crear el enlace simbólico (storage symlink) en cPanel.
 * Sube este archivo a la carpeta 'public' de tu API de Laravel y ejecútalo desde el navegador.
 * Ejemplo: http://contrataciones.avantebureaushipping.com/api/link.php
 */

$target = __DIR__ . '/../../../contrataciones-api/storage/app/public';
$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "El enlace simbólico ya existe.";
} else {
    if (symlink($target, $link)) {
        echo "¡Éxito! El enlace simbólico de 'storage' ha sido creado correctamente.";
    } else {
        echo "Error al crear el enlace simbólico. Verifica los permisos de las carpetas.";
    }
}
