<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Générer des données cohérentes
        $companyName = fake()->company();
        $companyDomain = strtolower(str_replace([' ', '&', '-', '.'], ['', 'and', '', ''], $companyName)).'.com';

        // Email de notification
        $notificationEmail = 'contact@'.$companyDomain;

        return [
            'company_name' => $companyName,
            'trade_name' => $companyName,
            'siret_number' => fake()->randomNumber(8),
            'address' => fake()->streetAddress(),
            'zip_code' => fake()->postcode(),
            'city' => fake()->city(),
            'subcontractor_of' => fake()->company(),
            'kbis_document' => fake()->mimeType(),
            'safety_document' => fake()->mimeType(),
            'security_document' => fake()->mimeType(),
            'badge_limit' => fake()->numberBetween(5, 15),
            'vehicle_pass_limit' => fake()->numberBetween(0, 10),
            'notification_email' => $notificationEmail,
            'slug' => fake()->uuid(),
            'is_airline_company' => fake()->boolean(),  
        ];
    }
}
