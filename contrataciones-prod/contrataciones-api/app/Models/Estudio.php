<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudio extends Model
{
    use HasFactory;

    protected $table = 'estudios';

    protected $fillable = [
        'empleado_id',
        'nivel',
        'institucion',
        'lugar',
        'fecha_inicio',
        'fecha_culminacion',
        'grado_titulo',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
