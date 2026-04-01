<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_empleado')->comment('Ej: Personal en Tierra, Gente de Mar');
            $table->string('cargo')->nullable();
            $table->string('puesto')->nullable();
            $table->string('email')->unique();
            $table->timestamps(); // Corresponde a fecha_creacion y fecha_modificacion
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
