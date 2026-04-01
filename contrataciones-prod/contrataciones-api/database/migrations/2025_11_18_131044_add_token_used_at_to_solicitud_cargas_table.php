<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // En el nuevo archivo de migración ..._add_token_used_at_...
    public function up(): void
    {
        Schema::table('solicitud_cargas', function (Blueprint $table) {
            // Añadimos la nueva columna después de 'estado'
            $table->timestamp('token_used_at')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_cargas', function (Blueprint $table) {
            $table->dropColumn('token_used_at');
        });
    }
};
