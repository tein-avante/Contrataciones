<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Elige un tipo de empleado aleatoriamente
        $tipoEmpleado = fake()->randomElement(['Personal en Tierra', 'Gente de Mar']);
        
        return [
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'tipo_empleado' => $tipoEmpleado,
            'cargo' => fake()->jobTitle(),
            'puesto' => ($tipoEmpleado == 'Gente de Mar') ? fake()->randomElement(['Capitán', 'Oficial de Cubierta', 'Marinero']) : null,
        ];
    }
}