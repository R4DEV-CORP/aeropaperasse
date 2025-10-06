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
        $referentFirstName = fake()->firstName();
        $referentLastName = fake()->lastName();
        $referentName = $referentFirstName . ' ' . $referentLastName;
        
        // Créer un email de notification cohérent avec la société
        $companyDomain = strtolower(str_replace([' ', '&', '-', '.'], ['', 'and', '', ''], $companyName)) . '.com';
        $notificationEmail = 'contact@' . $companyDomain;

        // Créer un email cohérent avec le nom du référent
        $referentEmail = strtolower($referentFirstName . '.' . $referentLastName . '@' . $companyDomain);
        
        return [
            'name' => $companyName,
            'referent_name' => $referentName,
            'referent_email' => $referentEmail,
            'badge_limit' => fake()->numberBetween(5, 25),
            'vehicle_pass_limit' => fake()->numberBetween(0, 10),
            'notification_email' => $notificationEmail,
            'company_name' => $companyName, // Même nom que 'name'
            'company_address' => fake()->address(),
            'company_phone' => fake()->phoneNumber(),
            'company_email' => $notificationEmail, // Même email que notification_email
        ];
    }
}
