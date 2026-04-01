<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Empleado;

$empleado = Empleado::with('idiomas')->first(); // O el ID que corresponda
if ($empleado) {
    echo "ID: " . $empleado->id . "\n";
    foreach ($empleado->idiomas as $idi) {
        echo "Idioma: " . $idi->idioma . "\n";
        echo "Habla: '" . $idi->habla . "'\n";
        echo "Lee: '" . $idi->lee . "'\n";
        echo "Escribe: '" . $idi->escribe . "'\n";
        echo "----------\n";
    }
} else {
    echo "No se encontró empleado.\n";
}
