<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\DatoFamiliar;
use App\Models\Estudio;
use App\Models\CursoEvento;
use App\Models\Idioma;
use App\Models\ExperienciaLaboral;
use App\Models\ReferenciaPersonal;
use Carbon\Carbon;

class EmpleadoOfertaSeeder extends Seeder
{
    public function run()
    {
        $nombres = ['Cap. Alejandro Vargas', 'Ing. Valentina Sifontes', 'Técn. Roberto Méndez'];
        $tipoEmpleados = ['Personal de Buque', 'Personal Administrativo-Operativo', 'Personal de Buque'];
        $cargos = ['Capitán de Altura', 'Gerente de Operaciones', 'Motorista de Primera'];

        foreach ($nombres as $index => $nombre) {
            $empleado = Empleado::create([
                'nombre' => $nombre,
                'email' => strtolower(str_replace([' ', '.'], '', $nombre)) . $index . '@avante.com',
                'tipo_empleado' => $tipoEmpleados[$index],
                'cargo' => $cargos[$index],
                'puesto' => $tipoEmpleados[$index] == 'Personal de Buque' ? 'Buque Tanque Avante Star' : 'Sede Principal Lechería',
                'cedula' => 'V-' . (10500000 + ($index * 1500000)),
                'cedula_marina' => $tipoEmpleados[$index] == 'Personal de Buque' ? 'DIM-' . (50000 + $index) : null,
                'telefono' => '+58 424-' . (7000000 + ($index * 111111)),
                'codigo_postal' => '6016',
                'fecha_nacimiento' => Carbon::now()->subYears(30 + ($index * 5))->format('Y-m-d'),
                'sexo' => $index == 1 ? 'Femenino' : 'Masculino',
                'tiene_hijos' => 'Sí',
                'estado_embarque' => $tipoEmpleados[$index] == 'Personal de Buque' ? 'A bordo' : null,
                'direccion' => 'Av. Intercomunal, Res. Las Palmeras, Apto ' . (10 + $index),
                'ciudad' => 'Lechería',
                'lugar_nacimiento' => 'Puerto La Cruz',
                'estado_civil' => $index == 0 ? 'Casado' : 'Soltero',
                'nacionalidad' => 'Venezolana',
                'estatura' => (1.70 + ($index * 0.05)) . 'm',
                'peso' => (70 + ($index * 8)) . 'kg',
                'tipo_sangre' => 'A+',
                'fecha_disponible' => Carbon::now()->format('Y-m-d'),
                'tipo_habitacion' => 'Apartamento',
                'caracteristicas_habitacion' => 'Propia, con todos los servicios básicos',
                'colegiacion_nro' => 'COL-' . (9000 + $index),
                'licencia_conductor_nro' => '5-' . rand(100000, 999999),
                'licencia_conductor_expiracion' => Carbon::now()->addYears(5)->format('Y-m-d'),
                'talla_pantalon' => '32',
                'talla_camisa' => 'M',
                'talla_zapato' => '42',
                'habilidades_destrezas' => 'Dominio de sistemas de navegación GMDSS, gestión de seguridad según código IGS, liderazgo de tripulación y manejo de emergencias marítimas.',
            ]);

            // --- 1. DATOS FAMILIARES (3 por empleado) ---
            DatoFamiliar::create([
                'empleado_id' => $empleado->id, 'nombre' => 'Susana de ' . $nombre, 'parentesco' => 'Esposa/o', 'edad' => 35, 'nacionalidad' => 'Venezolana', 'telefono' => '+58 414-0000001'
            ]);
            DatoFamiliar::create([
                'empleado_id' => $empleado->id, 'nombre' => 'Andrés ' . explode(' ', $nombre)[1], 'parentesco' => 'Hijo', 'edad' => 12, 'nacionalidad' => 'Venezolana', 'telefono' => 'N/A'
            ]);
            DatoFamiliar::create([
                'empleado_id' => $empleado->id, 'nombre' => 'Lucía ' . explode(' ', $nombre)[1], 'parentesco' => 'Hija', 'edad' => 8, 'nacionalidad' => 'Venezolana', 'telefono' => 'N/A'
            ]);

            // --- 2. ESTUDIOS REALIZADOS (De Primaria a Postgrado) ---
            $niveles = ['Primaria', 'Secundaria', 'Pregrado', 'Postgrado'];
            foreach ($niveles as $i => $nivel) {
                Estudio::create([
                    'empleado_id' => $empleado->id,
                    'nivel' => $nivel,
                    'institucion' => 'Institución Educativa ' . $nivel,
                    'lugar' => 'Anzoátegui',
                    'fecha_inicio' => Carbon::now()->subYears(25 - ($i * 5))->format('Y-m-d'),
                    'fecha_culminacion' => Carbon::now()->subYears(20 - ($i * 5))->format('Y-m-d'),
                    'grado_titulo' => $nivel == 'Pregrado' ? 'Licenciado / Ingeniero' : ($nivel == 'Postgrado' ? 'Magister' : 'Bachiller'),
                ]);
            }

            // --- 3. CURSOS Y OTROS EVENTOS (3 cursos) ---
            CursoEvento::create([
                'empleado_id' => $empleado->id, 'nombre_curso' => 'Omi 1.19 Supervivencia', 'institucion' => 'Escuela Náutica', 'fecha' => '2023-01-15', 'horas' => 40, 'certificado' => true
            ]);
            CursoEvento::create([
                'empleado_id' => $empleado->id, 'nombre_curso' => 'Omi 1.20 Prevención de Incendios', 'institucion' => 'Escuela Náutica', 'fecha' => '2023-02-10', 'horas' => 40, 'certificado' => true
            ]);
            CursoEvento::create([
                'empleado_id' => $empleado->id, 'nombre_curso' => 'Gestión de Recursos de Puente (BRM)', 'institucion' => 'Centro de Simulación', 'fecha' => '2023-05-20', 'horas' => 80, 'certificado' => true
            ]);

            // --- 4. IDIOMAS (Inglés y otro) ---
            Idioma::create([
                'empleado_id' => $empleado->id, 'idioma' => 'Inglés', 'habla' => 'Muy Bien', 'lee' => 'Muy Bien', 'escribe' => 'Muy Bien'
            ]);
            Idioma::create([
                'empleado_id' => $empleado->id, 'idioma' => 'Portugués', 'habla' => 'Regular', 'lee' => 'Bien', 'escribe' => 'Poco'
            ]);

            // --- 5. EXPERIENCIA LABORAL (3 experiencias) ---
            for ($e = 1; $e <= 3; $e++) {
                ExperienciaLaboral::create([
                    'empleado_id' => $empleado->id,
                    'empresa' => 'Naviera Internacional ' . $e,
                    'direccion_telefono' => 'Av. Principal Local ' . $e . ', Telf: 0281-281000' . $e,
                    'fecha_ingreso' => Carbon::now()->subYears(15 - ($e * 4))->format('Y-m-d'),
                    'fecha_retiro' => Carbon::now()->subYears(12 - ($e * 4))->format('Y-m-d'),
                    'sueldo_inicial' => 1200 * $e,
                    'sueldo_final' => 1800 * $e,
                    'cargo_inicial' => 'Oficial de guardia',
                    'cargo_final' => 'Primer Oficial',
                    'nombre_supervisor' => 'Cap. Roberto Jiménez',
                    'motivo_retiro' => 'Aceptó nueva oferta en Avante',
                ]);
            }

            // --- 6. REFERENCIAS PERSONALES (3 referencias) ---
            for ($r = 1; $r <= 3; $r++) {
                ReferenciaPersonal::create([
                    'empleado_id' => $empleado->id,
                    'nombre' => 'Referente ' . $r . ' de ' . explode(' ', $nombre)[1],
                    'profesion' => $r == 1 ? 'Ingeniero' : 'Abogado',
                    'direccion' => 'Urb. El Morro, Calle ' . $r,
                    'telefono' => '+58 416-' . rand(1000000, 9999999),
                ]);
            }
        }
    }
}
