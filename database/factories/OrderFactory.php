<?php

namespace Database\Factories;

use Database\Seeders\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pour le modèle Order
class OrderFactory extends Factory
{
    // Définit l'état par défaut du modèle
    public function definition(): array
    {
        $orderStats = Status::cases();
        $status = fake()->randomElement($orderStats);

        return [
            'order_num' => fake()->unique()->randomNumber(5).fake()->unique()->randomNumber(6),
            'title' => fake()->slug(),
            'description' => fake()->sentences(6, true),
            'status' => $status,
            'cost' => array_search($status, $orderStats) > array_search('BON_DE_COMMANDE_SIGNE', $orderStats) ? fake()->randomFloat(2, 0, 999999999) : null, // TODO S'il y a une erreur à propos du coût c'est que les 12 chiffres sont à prendre dans les négatifs et les positifs
            'quote_num' => fake()->randomLetter().fake()->unique()->randomNumber(7),
        ];
    }
}
