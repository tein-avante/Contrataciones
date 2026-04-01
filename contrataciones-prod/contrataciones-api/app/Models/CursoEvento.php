<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoEvento extends Model
{
    use HasFactory;

    protected $table = 'cursos_eventos';

    protected $fillable = [
        'empleado_id',
        'nombre_curso',
        'institucion',
        'fecha',
        'horas',
        'certificado',
    ];

    protected $casts = [
        'certificado' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
