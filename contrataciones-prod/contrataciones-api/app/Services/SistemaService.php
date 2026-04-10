<?php

namespace App\Services;

use App\Models\SistemaConfig;

class SistemaService
{
    /**
     * Inicializa los valores en la base de datos si no existen.
     */
    public static function inicializar()
    {
        // Asegurar que exista la versión (2.1)
        SistemaConfig::firstOrCreate(
            ['clave' => 'version'],
            ['valor' => '2.1']
        );

        // Asegurar que existan las operaciones (comenzando en 0)
        SistemaConfig::firstOrCreate(
            ['clave' => 'operaciones'],
            ['valor' => '0']
        );
    }

    /**
     * Incrementa el contador global de operaciones.
     */
    public static function incrementarOperaciones()
    {
        // En caso de que no esté inicializado, buscar o crearlo con 0
        $operaciones = SistemaConfig::firstOrCreate(
            ['clave' => 'operaciones'],
            ['valor' => '0']
        );

        $valorActual = (int) $operaciones->valor;
        $operaciones->update(['valor' => (string)($valorActual + 1)]);
    }

    /**
     * Obtiene la información actual (versión y operaciones).
     */
    public static function obtenerInfo()
    {
        $version = SistemaConfig::firstOrCreate(
            ['clave' => 'version'],
            ['valor' => '2.2']
        );
        $operaciones = SistemaConfig::firstOrCreate(
            ['clave' => 'operaciones'],
            ['valor' => '0']
        );

        return [
            'version' => $version->valor,
            'operaciones' => $operaciones->valor,
        ];
    }
}
