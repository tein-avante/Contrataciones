<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'empleado_id',
        'tipo_documento_id',
        'archivo',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
    ];

    // RELACIÓN INVERSA: Un documento pertenece a un empleado.
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // RELACIÓN INVERSA: Un documento es de un tipo de documento.
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }
}