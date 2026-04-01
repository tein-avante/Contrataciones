<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoDocumento; // Importamos el modelo

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoDocumento::create([
            'nombre' => 'Cédula de Identidad',
            'periodo_alerta' => 30, // Alerta 30 días antes de vencer
            'requiere_archivo' => true
        ]);

        TipoDocumento::create([
            'nombre' => 'Pasaporte',
            'periodo_alerta' => 90,
            'requiere_archivo' => true
        ]);
        
        TipoDocumento::create([
            'nombre' => 'Licencia de Conducir',
            'periodo_alerta' => 45,
            'requiere_archivo' => true
        ]);

        TipoDocumento::create([
            'nombre' => 'Certificado de Antecedentes',
            'periodo_alerta' => 180,
            'requiere_archivo' => true
        ]);
    }
}