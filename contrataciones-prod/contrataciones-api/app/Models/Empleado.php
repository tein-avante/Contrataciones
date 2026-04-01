<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo Empleado
 *
 * Representa la entidad central del sistema Gerencia de Contrataciones.
 * Gestiona la información laboral y personal del trabajador.
 * Actúa como punto de anclaje para el expediente digital (documentos)
 * y el flujo de solicitudes.
 */
class Empleado extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'empleados';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'tipo_empleado', // 'Personal Administrativo-Operativo' o 'Personal de Buque'
        'cargo',
        'puesto',
        'email',
        'cedula',
        'cedula_marina',
        'telefono',
        'codigo_postal',
        'fecha_nacimiento',
        'sexo',
        'tiene_hijos',
        'estado_embarque',
        'direccion',
        'ciudad',
        'lugar_nacimiento',
        'estado_civil',
        'nacionalidad',
        'estatura',
        'peso',
        'tipo_sangre',
        'fecha_disponible',
        'tipo_habitacion',
        'caracteristicas_habitacion',
        'colegiacion_nro',
        'licencia_conductor_nro',
        'licencia_conductor_expiracion',
        'talla_pantalon',
        'talla_camisa',
        'talla_zapato',
        'habilidades_destrezas',
        'foto',
    ];

    public function datosFamiliares()
    {
        return $this->hasMany(DatoFamiliar::class);
    }

    public function estudios()
    {
        return $this->hasMany(Estudio::class);
    }

    public function cursosEventos()
    {
        return $this->hasMany(CursoEvento::class);
    }

    public function idiomas()
    {
        return $this->hasMany(Idioma::class);
    }

    public function experienciasLaborales()
    {
        return $this->hasMany(ExperienciaLaboral::class);
    }

    public function referenciasPersonales()
    {
        return $this->hasMany(ReferenciaPersonal::class);
    }

    /**
     * Relación: Expediente Digital.
     * Un empleado posee múltiples documentos (Pasaporte, Cédula, Títulos).
     * Esta relación permite verificar la vigencia de la documentación
     * para el cálculo del semáforo.
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'empleado_id');
    }

    /**
     * Relación: Historial de Notificaciones.
     * Permite rastrear todas las alertas y avisos enviados específicamente
     * a este empleado (ej: correos de vencimiento).
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'empleado_id');
    }

    /**
     * Relación: Solicitudes de Carga.
     * Un empleado puede tener múltiples solicitudes (tickets) para subir archivos.
     * Permite auditar el cumplimiento de los requerimientos por parte del personal.
     */
    public function solicitudesCarga()
    {
        return $this->hasMany(SolicitudCarga::class);
    }
}
