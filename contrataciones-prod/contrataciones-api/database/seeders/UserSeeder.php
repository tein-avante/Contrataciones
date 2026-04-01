<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Super Admin
        User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@sgrh.com',
            'password' => 'password', // Texto plano, el modelo lo cifra
            'role' => 'admin',
        ]);

        // 2. Crear el Analista
        User::create([
            'name' => 'Analista General',
            'email' => 'analista@sgrh.com',
            'password' => 'password', // Texto plano, el modelo lo cifra
            'role' => 'analyst',
        ]);
    }
}
