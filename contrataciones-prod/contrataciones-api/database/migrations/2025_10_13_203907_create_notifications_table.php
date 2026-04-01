<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Una notificación puede ser para un usuario O un empleado.
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('cascade');

            // Puede estar asociada a un documento específico.
            $table->foreignId('documento_id')->nullable()->constrained('documentos')->onDelete('set null');

            $table->text('mensaje');
            $table->timestamp('read_at')->nullable(); // La columna para saber si fue leída
            $table->dateTime('fecha_aviso');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
