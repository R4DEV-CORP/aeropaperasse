<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coworker>
 */
class CoworkerFactory extends Factory
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
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => fake()->phoneNumber(),
            'has_leave' => false,
            'departure_date' => null,
            'can_access_formation' => fake()->boolean(),
        ];
    }
}
