<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactClient>
 */
class ContactClientFactory extends Factory
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
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => fake()->phoneNumber(),
            'role' => fake()->randomElement(['hr', 'safety', 'security', 'manager']),
        ];
    }
}
