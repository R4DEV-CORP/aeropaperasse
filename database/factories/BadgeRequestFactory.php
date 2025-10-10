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
        return [
            'status' => fake()->randomElement(['draft','pending_rem', 'rejected_rem', 'pending_adp', 'approved_adp', 'rejected_adp', 'pending_fabrication', 'ready_for_delivery']),
            'selfie_photo' => fake()->mimeType(),
            'identification_card' => fake()->mimeType(),
            'activity_authorization' => fake()->mimeType(),
            'for_document' => fake()->mimeType(),
            'formation_certificate_document' => fake()->mimeType(),
            'invoice_document' => fake()->mimeType(),
            'application_authorization' => fake()->boolean(),
            'validate_training' => fake()->boolean(),
        ];
    }
}
