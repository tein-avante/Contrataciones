<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCarga extends Model
{
    use HasFactory;

    protected $table = 'solicitud_cargas';

    protected $fillable = [
        'ticket',
        'estado',
        'motivo_rechazo',
        'observacion',
        'fecha_expiracion',
        'usuario_id',
        'empleado_id',
        'documento_id',
        'tipo_documento_id',
        'token_used_at',
    ];

    protected $casts = [
        'fecha_expiracion' => 'date:Y-m-d',
        'token_used_at' => 'datetime',
    ];

    // --- RELACIONES ---

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

}
