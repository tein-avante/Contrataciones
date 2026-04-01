<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienciaLaboral extends Model
{
    use HasFactory;

    protected $table = 'experiencias_laborales';

    protected $fillable = [
        'empleado_id',
        'empresa',
        'direccion_telefono',
        'fecha_ingreso',
        'fecha_retiro',
        'sueldo_inicial',
        'sueldo_final',
        'cargo_inicial',
        'cargo_final',
        'nombre_supervisor',
        'motivo_retiro',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
