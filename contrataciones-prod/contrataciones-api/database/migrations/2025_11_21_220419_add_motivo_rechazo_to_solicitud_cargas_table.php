<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitud_cargas', function (Blueprint $table) {
            // Añadimos la nueva columna para el motivo del rechazo.
            // La hacemos 'nullable' porque solo tendrá valor cuando se rechace.
            // La colocamos después de 'observacion' por orden lógico.
            $table->text('motivo_rechazo')->nullable()->after('observacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_cargas', function (Blueprint $table) {
            // Esto permite revertir el cambio si es necesario
            $table->dropColumn('motivo_rechazo');
        });
    }
};
