<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pour le modèle User
class UserFactory extends Factory
{
    // Définit l'état par défaut du modèle
    public function definition(): array
    {
        return [
            'last_name' => strtoupper(fake()->lastName()),
            'first_name' => fake()->firstName(),
            'login' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
        ];
    }

    // Configure la factory du modèle
    //    public function configure(): static
    //    {
    //
    //        return $this->afterMaking(function (User $user) {
    //
    //            // ...
    //
    //        })->afterCreating(function (User $user) {});
    //
    //    }
}
