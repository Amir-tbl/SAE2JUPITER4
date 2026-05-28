<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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

    /**
     * Configure the model factory.
     */
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
