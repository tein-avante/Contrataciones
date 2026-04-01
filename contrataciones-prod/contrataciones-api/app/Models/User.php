<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- El Trait más importante para nuestra API
use App\Models\Notification;

class User extends Authenticatable
{
    // --- USA ESTOS TRAITS ---
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- Nombre correcto de la columna en BD
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean', // <-- Le decimos a Laravel que 'activo' es un booleano
    ];

    public function solicitudesCarga()
    {
        return $this->hasMany(SolicitudCarga::class, 'usuario_id');
    }


    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
