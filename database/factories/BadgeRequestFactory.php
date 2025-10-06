<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BadgeRequest>
 */
class BadgeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = strtolower($firstName . '.' . $lastName . '@gmail.com');
        return [
            'airport' => fake()->randomElement(['CDG', 'ORY', 'BVA']),
            'nom' => $lastName,
            'prenom' => $firstName,
            'email' => $email,
            'telephone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['pending_rem', 'rejected_rem', 'pending_adp', 'approved_adp', 'rejected_adp', 'pending_fabrication', 'ready_for_delivery']),
            'est_habilitation' => fake()->boolean(),
        ];
    }
}
