<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmpleadoController extends Controller
{
    // GET /api/empleados
    public function index()
    {
        // 1. Traemos los empleados CON sus documentos y los tipos de documento
        // (El 'with' es vital para no hacer cientos de consultas a la BD)
        $empleados = Empleado::with('documentos.tipoDocumento')->latest()->get();

        // 2. Procesamos cada empleado para calcular su "Semáforo"
        $empleados->map(function ($empleado) {
            $status = 'ok'; // Por defecto verde

            foreach ($empleado->documentos as $documento) {
                // Si no tiene fecha de vencimiento, lo saltamos
                if (!$documento->fecha_vencimiento)
                    continue;

                $vence = Carbon::parse($documento->fecha_vencimiento);
                $hoy = Carbon::today();

                // Días restantes (puede ser negativo si ya venció)
                $diasRestantes = $hoy->diffInDays($vence, false);

                // Lógica de Prioridad: El error más grave sobreescribe a los demás

                // CASO 1: VENCIDO (Rojo) - Prioridad Máxima
                if ($diasRestantes < 0) {
                    $status = 'critical';
                    break; // Si ya encontramos uno vencido, el empleado está en estado crítico. Dejamos de buscar.
                }

                // CASO 2: POR VENCER (Amarillo)
                // Solo aplicamos si el estado actual no es ya 'critical'
                if ($status !== 'critical' && $documento->tipoDocumento && $diasRestantes <= $documento->tipoDocumento->periodo_alerta) {
                    $status = 'warning';
                // No hacemos 'break' porque podría haber otro documento vencido más adelante en el ciclo
                }
            }

            // Agregamos este atributo temporal al objeto empleado para que Vue lo lea
            $empleado->semaforo = $status;
            return $empleado;
        });

        return response()->json($empleados);
    }

    // POST /api/empleados
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:empleados,email',
            'tipo_empleado' => 'required|string',
            'cargo' => 'nullable|string',
            'puesto' => 'nullable|string',
            'cedula' => 'required|string|max:20', // Obligatoria para todos
            'cedula_marina' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'codigo_postal' => 'nullable|string|max:10',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string',
            'tiene_hijos' => 'nullable|string',
            'estado_embarque' => 'nullable|string',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|max:255',
            'nacionalidad' => 'nullable|string|max:255',
            'estatura' => 'nullable|string|max:255',
            'peso' => 'nullable|string|max:255',
            'tipo_sangre' => 'nullable|string|max:20',
            'fecha_disponible' => 'nullable|date',
            'tipo_habitacion' => 'nullable|string|max:255',
            'caracteristicas_habitacion' => 'nullable|string|max:255',
            'colegiacion_nro' => 'nullable|string|max:255',
            'licencia_conductor_nro' => 'nullable|string|max:255',
            'licencia_conductor_expiracion' => 'nullable|date',
            'talla_pantalon' => 'nullable|string|max:50',
            'talla_camisa' => 'nullable|string|max:50',
            'talla_zapato' => 'nullable|string|max:50',
            'habilidades_destrezas' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos', 'public');
            $validatedData['foto'] = $path;
        }

        if ($validatedData['tipo_empleado'] !== 'Personal de Buque') {
            $validatedData['estado_embarque'] = null;
        }

        $empleado = Empleado::create($validatedData);
        return response()->json($empleado, 201); // 201 = Created
    }

    // GET /api/empleados/{empleado}
    public function show(Empleado $empleado)
    {
        // 1. Cargar las relaciones necesarias
        $empleado->load([
            'documentos.tipoDocumento',
            'solicitudesCarga',
            'datosFamiliares',
            'estudios',
            'cursosEventos',
            'idiomas',
            'experienciasLaborales',
            'referenciasPersonales'
        ]);

        // 2. Calcular el estado visual al momento
        foreach ($empleado->documentos as $documento) {

            // Lógica por defecto: el estado visual es igual al estado de la BD
            $documento->estado_visual = $documento->estado;

            // Solo si dice "Vigente" y tiene fecha, verificamos si matemáticamente ya venció
            if ($documento->estado === 'Vigente' && $documento->fecha_vencimiento) {

                $vence = Carbon::parse($documento->fecha_vencimiento);
                $hoy = Carbon::today();

                // false indica que queremos diferencias negativas si la fecha ya pasó
                $diasRestantes = $hoy->diffInDays($vence, false);

                if ($diasRestantes < 0) {
                    // Está vencido matemáticamente, aunque la BD diga Vigente.
                    // Creamos esta propiedad temporal 'estado_visual' solo para enviarla al frontend.
                    $documento->estado_visual = 'Vencido';
                }
                elseif ($documento->tipoDocumento && $diasRestantes <= $documento->tipoDocumento->periodo_alerta) {
                    $documento->estado_visual = 'Por Vencer';
                }
            }
        }

        return response()->json($empleado);
    }

    // PUT /api/empleados/{empleado}
    public function update(Request $request, Empleado $empleado)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:empleados,email,' . $empleado->id,
            'tipo_empleado' => 'required|string',
            'cargo' => 'nullable|string',
            'puesto' => 'nullable|string',
            'cedula' => 'required|string|max:20',
            'cedula_marina' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'codigo_postal' => 'nullable|string|max:10',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string',
            'tiene_hijos' => 'nullable|string',
            'estado_embarque' => 'nullable|string',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|max:255',
            'nacionalidad' => 'nullable|string|max:255',
            'estatura' => 'nullable|string|max:255',
            'peso' => 'nullable|string|max:255',
            'tipo_sangre' => 'nullable|string|max:20',
            'fecha_disponible' => 'nullable|date',
            'tipo_habitacion' => 'nullable|string|max:255',
            'caracteristicas_habitacion' => 'nullable|string|max:255',
            'colegiacion_nro' => 'nullable|string|max:255',
            'licencia_conductor_nro' => 'nullable|string|max:255',
            'licencia_conductor_expiracion' => 'nullable|date',
            'talla_pantalon' => 'nullable|string|max:50',
            'talla_camisa' => 'nullable|string|max:50',
            'talla_zapato' => 'nullable|string|max:50',
            'habilidades_destrezas' => 'nullable|string',
            'foto' => $request->hasFile('foto') ? 'image|mimes:jpeg,png,jpg|max:2048' : 'nullable',
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($empleado->foto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($empleado->foto);
            }
            $path = $request->file('foto')->store('fotos', 'public');
            $validatedData['foto'] = $path;
        }

        if ($validatedData['tipo_empleado'] !== 'Personal de Buque') {
            $validatedData['estado_embarque'] = null;
        }

        $empleado->update($validatedData);
        return response()->json($empleado);
    }

    // DELETE /api/empleados/{empleado}
    public function destroy(Empleado $empleado)
    {
        foreach ($empleado->documentos as $documento) {
            if ($documento->archivo) {
                Storage::disk('public')->delete($documento->archivo);
            }
            $documento->delete();
        }
        $empleado->delete();
        return response()->json(null, 204);
    }

    // GET /api/empleados/{empleado}/pdf
    public function generatePdf(Empleado $empleado)
    {
        $empleado->load([
            'datosFamiliares',
            'estudios',
            'cursosEventos',
            'idiomas',
            'experienciasLaborales',
            'referenciasPersonales'
        ]);

        // Usamos Barryvdh\DomPDF\Facade\Pdf o shorthand PDF si está registrado
        // Para evitar errores si no está instalado, usamos el nombre completo de la clase si existe
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return response()->json(['error' => 'Módulo PDF no instalado. Por favor ejecute composer require barryvdh/laravel-dompdf'], 500);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.oferta-servicio', compact('empleado'));

        return $pdf->download('Oferta_Servicio_' . str_replace(' ', '_', $empleado->nombre) . '.pdf');
    }

    // GET /api/empleados/export/excel
    public function exportExcel(Request $request)
    {
        $tipo = $request->query('tipo'); // 'Personal de Buque', 'Personal Administrativo-Operativo' o null (todos)

        $query = Empleado::query();

        if ($tipo) {
            $query->where('tipo_empleado', $tipo);
        }

        $empleados = $query->latest()->get();

        $fileName = 'Reporte_Personal_' . ($tipo ? str_replace(' ', '_', $tipo) : 'General') . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Nombre Completo',
            'Email',
            'Cédula',
            'Cédula Marina',
            'Teléfono',
            'Tipo de Empleado',
            'Cargo',
            'Puesto',
            'Fecha de Nacimiento',
            'Sexo',
            'Código Postal',
            '¿Tiene Hijos?',
            'Estado de Embarque',
            'Dirección de Habitación',
            'Ciudad',
            'Lugar de Nacimiento',
            'Estado Civil',
            'Nacionalidad',
            'Estatura',
            'Peso',
            'Tipo de Sangre',
            'Fecha Disponible',
            'Tipo de Habitación',
            'Características Habitación',
            'Colegiación Nº',
            'Licencia de Conductor',
            'Expiración Licencia',
            'Talla Pantalón',
            'Talla Camisa',
            'Talla Zapato'
        ];

        $callback = function() use($empleados, $columns) {
            $file = fopen('php://output', 'w');
            
            // BOM para que Excel detecte UTF-8 correctamente
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, $columns, ';');

            foreach ($empleados as $empleado) {
                fputcsv($file, [
                    $empleado->nombre,
                    $empleado->email,
                    $empleado->cedula,
                    $empleado->cedula_marina,
                    $empleado->telefono,
                    $empleado->tipo_empleado,
                    $empleado->cargo,
                    $empleado->puesto,
                    $empleado->fecha_nacimiento,
                    $empleado->sexo,
                    $empleado->codigo_postal,
                    $empleado->tiene_hijos,
                    $empleado->estado_embarque,
                    $empleado->direccion,
                    $empleado->ciudad,
                    $empleado->lugar_nacimiento,
                    $empleado->estado_civil,
                    $empleado->nacionalidad,
                    $empleado->estatura,
                    $empleado->peso,
                    $empleado->tipo_sangre,
                    $empleado->fecha_disponible,
                    $empleado->tipo_habitacion,
                    $empleado->caracteristicas_habitacion,
                    $empleado->colegiacion_nro,
                    $empleado->licencia_conductor_nro,
                    $empleado->licencia_conductor_expiracion,
                    $empleado->talla_pantalon,
                    $empleado->talla_camisa,
                    $empleado->talla_zapato,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
