<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiencias_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->string('empresa');
            $table->string('direccion_telefono')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->decimal('sueldo_inicial', 15, 2)->nullable();
            $table->decimal('sueldo_final', 15, 2)->nullable();
            $table->string('cargo_inicial')->nullable();
            $table->string('cargo_final')->nullable();
            $table->string('nombre_supervisor')->nullable();
            $table->string('motivo_retiro')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencias_laborales');
    }
};
