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
            'manager_role' => fake()->randomElement(['hr', 'safety', 'security', 'manager']),
            'description' => fake()->text(),
            'person_count' => fake()->numberBetween(1, 5),
            'vehicule_count' => fake()->numberBetween(0, 5),
            'customer_names' => fake()->company(),
            'customer_certificate_document' => fake()->mimeType(),
            'prefectural_agreement_document' => fake()->mimeType(),
            'iata_contract_document' => fake()->mimeType(),
            'cta_document' => fake()->mimeType(),
            'status' => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'previous_status' => fake()->randomElement(['draft', 'pending', 'approved', 'rejected']),
        ];
    }
}
