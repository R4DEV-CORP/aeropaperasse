<?php

namespace Database\Factories;

use App\Models\ActivityRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehiclePass>
 */
class VehiclePassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'rejected', 'approved']);

        $timestamps = [];
        if ($status === 'pending') {
            $timestamps['pending_at'] = now();
        } elseif ($status === 'rejected') {
            $timestamps['rejected_at'] = now();
        } elseif ($status === 'approved') {
            $timestamps['approved_at'] = now();
        }

        return [
            'airport' => fake()->randomElement(['ORY', 'CDG', 'LBG']),
            'plate_number' => fake()->regexify('[A-Z]{2}-[0-9]{3}-[A-Z]{2}'),
            'car_brand' => fake()->randomElement(['Renault', 'Peugeot', 'Citroën', 'BMW', 'Mercedes', 'Volkswagen', 'Toyota', 'Ford']),
            'status' => $status,
            'previous_status' => fake()->optional()->randomElement(['pending', 'rejected']),
            'reject_reason' => $status === 'rejected' ? fake()->optional()->sentence() : null,
            'certificate_of_registration' => fake()->optional()->mimeType(),
            'company_stamp' => fake()->optional()->mimeType(),
            ...$timestamps,
        ];
    }

    /**
     * Indique que le laissez-passer est lié à une demande d'activité
     */
    public function forActivityRequest(ActivityRequest $activityRequest): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_request_id' => $activityRequest->id,
            'client_id' => $activityRequest->client_id,
        ]);
    }

    /**
     * Indique que le laissez-passer n'est pas lié à une demande d'activité (pour les anciens)
     */
    public function withoutActivityRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_request_id' => null,
        ]);
    }
}
