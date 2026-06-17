<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pour le modèle Log
class LogFactory extends Factory
{
    // Définit l'état par défaut du modèle
    public function definition(): array
    {
        return [
            'content' => fake()->sentences(rand(1, 4), true),
        ];
    }
}
