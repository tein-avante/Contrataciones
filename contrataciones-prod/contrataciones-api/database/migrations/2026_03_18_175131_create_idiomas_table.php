<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idiomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->string('idioma');
            $table->string('habla')->default('Poco')->comment('Bien, Regular, Poco');
            $table->string('lee')->default('Poco')->comment('Bien, Regular, Poco');
            $table->string('escribe')->default('Poco')->comment('Bien, Regular, Poco');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idiomas');
    }
};
