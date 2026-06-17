<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pour le modèle Package
class PackageFactory extends Factory
{
    // Définit l'état par défaut du modèle
    public function definition(): array
    {
        $shipping_date = fake()->dateTimeThisYear('+3 years');
        if ($shipping_date->getTimestamp() > now()->getTimestamp()) {
            $shipping_date = null;
        }

        return [
            'name' => fake()->title(),
            'cost' => fake()->randomFloat(2, 0, 12), // TODO S'il y a une erreur à propos du coût c'est que les 12 chiffres sont à prendre dans les négatifs et les positifs
            'expected_delivery_time' => fake()->numberBetween(2, 52).' '.fake()->randomElement(['jours', 'semaines', 'mois']),
            'shipping_date' => $shipping_date,
        ];
    }
}
