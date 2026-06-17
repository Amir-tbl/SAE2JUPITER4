<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

// Factory pour le modèle Supplier
class SupplierFactory extends Factory
{
    // Définit l'état par défaut du modèle
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'siret' => fake()->unique()->randomNumber(7).fake()->unique()->randomNumber(7),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'contact_name' => fake()->name(),
            'speciality' => fake()->domainWord(),
            'note' => fake()->sentences(rand(1, 15), true),
            'is_valid' => fake()->boolean(),
        ];
    }
}
