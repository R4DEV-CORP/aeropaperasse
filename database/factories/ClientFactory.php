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
        return [
            'name' => 'R4Web',
            'referent_name' => 'R4Web',
            'referent_email' => 'contact@r4web.fr',
            'badge_limit' => 100,
            'vehicle_pass_limit' => 100,
            'notification_email' => 'contact@r4web.fr',
            'company_name' => 'R4Web',
            'company_address' => '123 Rue de la Paix, Paris, France',
            'company_phone' => '0674859641',
            'company_email' => 'contact@r4web.fr',
        ];
    }
}
