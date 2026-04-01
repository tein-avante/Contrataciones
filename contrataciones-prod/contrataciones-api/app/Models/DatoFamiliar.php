<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatoFamiliar extends Model
{
    use HasFactory;

    protected $table = 'datos_familiares';

    protected $fillable = [
        'empleado_id',
        'nombre',
        'parentesco',
        'edad',
        'nacionalidad',
        'telefono',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
