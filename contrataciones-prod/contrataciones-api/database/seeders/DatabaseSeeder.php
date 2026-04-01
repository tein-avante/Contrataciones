<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a los seeders en el orden que necesitemos
        $this->call([
            TipoDocumentoSeeder::class,
            UserSeeder::class,
        ]);
    }
}
