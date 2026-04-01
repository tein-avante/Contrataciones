<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'empleado_id',
        'documento_id',
        'mensaje',
        'read_at',
        'fecha_aviso',
    ];

    // RELACIÓN INVERSA: Una notificación pertenece a un empleado.
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // RELACIÓN INVERSA: Una notificación puede pertenecer a un documento.
    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }
}
