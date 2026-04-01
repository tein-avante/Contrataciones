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
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('email');
            $table->string('ciudad')->nullable()->after('direccion');
            $table->string('lugar_nacimiento')->nullable()->after('ciudad');
            $table->string('estado_civil')->nullable()->after('lugar_nacimiento');
            $table->string('nacionalidad')->nullable()->after('estado_civil');
            $table->string('estatura')->nullable()->after('nacionalidad');
            $table->string('peso')->nullable()->after('estatura');
            $table->string('tipo_sangre')->nullable()->after('peso');
            $table->date('fecha_disponible')->nullable()->after('tipo_sangre');
            $table->string('tipo_habitacion')->nullable()->after('fecha_disponible');
            $table->string('caracteristicas_habitacion')->nullable()->after('tipo_habitacion');
            $table->string('colegiacion_nro')->nullable()->after('caracteristicas_habitacion');
            $table->string('licencia_conductor_nro')->nullable()->after('colegiacion_nro');
            $table->date('licencia_conductor_expiracion')->nullable()->after('licencia_conductor_nro');
            $table->string('talla_pantalon')->nullable()->after('licencia_conductor_expiracion');
            $table->string('talla_camisa')->nullable()->after('talla_pantalon');
            $table->string('talla_zapato')->nullable()->after('talla_camisa');
            $table->text('habilidades_destrezas')->nullable()->after('talla_zapato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'direccion',
                'ciudad',
                'lugar_nacimiento',
                'estado_civil',
                'nacionalidad',
                'estatura',
                'peso',
                'tipo_sangre',
                'fecha_disponible',
                'tipo_habitacion',
                'caracteristicas_habitacion',
                'colegiacion_nro',
                'licencia_conductor_nro',
                'licencia_conductor_expiracion',
                'talla_pantalon',
                'talla_camisa',
                'talla_zapato',
                'habilidades_destrezas'
            ]);
        });
    }
};
