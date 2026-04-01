<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('cedula')->nullable()->after('email');
            $table->string('cedula_marina')->nullable()->after('cedula');
            $table->string('telefono')->nullable()->after('cedula_marina');
            $table->string('codigo_postal')->nullable()->after('telefono');
            $table->date('fecha_nacimiento')->nullable()->after('codigo_postal');
            $table->string('sexo')->nullable()->after('fecha_nacimiento');
            $table->string('tiene_hijos')->default('No')->after('sexo');
            $table->string('estado_embarque')->nullable()->after('tiene_hijos'); // 'A bordo' o 'En tierra'
        });

        // Actualizar datos existentes
        DB::table('empleados')->where('tipo_empleado', 'Personal en Tierra')->update(['tipo_empleado' => 'Personal Administrativo-Operativo']);
        DB::table('empleados')->where('tipo_empleado', 'Gente de Mar')->update(['tipo_empleado' => 'Personal de Buque']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir nombres
        DB::table('empleados')->where('tipo_empleado', 'Personal Administrativo-Operativo')->update(['tipo_empleado' => 'Personal en Tierra']);
        DB::table('empleados')->where('tipo_empleado', 'Personal de Buque')->update(['tipo_empleado' => 'Gente de Mar']);

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'cedula',
                'cedula_marina',
                'telefono',
                'codigo_postal',
                'fecha_nacimiento',
                'sexo',
                'tiene_hijos',
                'estado_embarque'
            ]);
        });
    }
};
