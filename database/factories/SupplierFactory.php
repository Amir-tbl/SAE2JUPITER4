<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
