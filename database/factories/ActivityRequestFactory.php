<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityRequest>
 */
class ActivityRequestFactory extends Factory
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
        $email = strtolower($firstName.'.'.$lastName.'@gmail.com');

        return [
            'manager_firstname' => $firstName,
            'manager_lastname' => $lastName,
            'manager_email' => $email,
            'manager_phone' => fake()->phoneNumber(),
            'manager_role' => fake()->randomElement(['Chef de chantier', 'Chef de site', 'Directeur', "Chef d'équipe"]),
            'description' => fake()->text(),
            'person_count' => fake()->numberBetween(1, 3),
            'vehicule_count' => fake()->numberBetween(0, 3),
            'customer_names' => fake()->company(),
            'aao_request_document' => fake()->mimeType(),
            'kbis_document' => fake()->mimeType(),
            'term_document' => fake()->mimeType(),
            'safety_referent_document' => fake()->mimeType(),
            'cta_document' => fake()->mimeType(),
            'status' => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'previous_status' => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'airport' => fake()->randomElement(['ORY', 'CDG', 'LBG']),
        ];
    }
}
