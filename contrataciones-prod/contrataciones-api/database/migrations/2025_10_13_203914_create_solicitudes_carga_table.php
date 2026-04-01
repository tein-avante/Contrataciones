<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('solicitud_cargas', function (Blueprint $table) {
        $table->id();

        // Quién hizo la solicitud (el analista/usuario)
        $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');

        // Para quién es la solicitud (el empleado)
        $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');

        // Opcional: si la solicitud es para actualizar un documento existente
        $table->foreignId('documento_id')->nullable()->constrained('documentos')->onDelete('set null');

        $table->date('fecha_expiracion'); // Fecha límite para el empleado
        $table->string('ticket')->unique();   // Un identificador único para la solicitud
        $table->string('estado')->default('Pendiente'); // Ej: Pendiente, Completada, Vencida
        $table->text('observacion')->nullable(); // Instrucciones para el empleado

        $table->timestamps();
    });
}
};
