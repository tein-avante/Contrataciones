<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'tipos_documento';

    protected $fillable = [
        'nombre',
        'periodo_alerta',
        'requiere_archivo',
    ];

    // RELACIÓN: Un tipo de documento puede estar asociado a muchos documentos.
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'tipo_documento_id');
    }
}
