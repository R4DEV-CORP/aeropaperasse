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
        $company = fake()->company();
        return [
            'renouvellement' => fake()->boolean(),
            'status' => 'pending',
            'raison_sociale' => $company,
            'nom_commercial' => $company,
            'responsable_nom' => fake()->lastName(),
            'responsable_prenom' => fake()->firstName(),
            'responsable_email' => fake()->email(),
            'responsable_telephone' => fake()->phoneNumber(),

        ];
    }
}
