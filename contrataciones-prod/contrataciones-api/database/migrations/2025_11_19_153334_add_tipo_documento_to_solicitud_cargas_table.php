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
            $table->foreignId('tipo_documento_id')->nullable()->after('documento_id')->constrained('tipos_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_cargas', function (Blueprint $table) {
            //
        });
    }
};
