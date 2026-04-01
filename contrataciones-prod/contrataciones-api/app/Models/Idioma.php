<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idioma extends Model
{
    use HasFactory;

    protected $table = 'idiomas';

    protected $fillable = [
        'empleado_id',
        'idioma',
        'habla',
        'lee',
        'escribe',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
