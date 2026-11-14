<?php

namespace Database\Factories;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'whatsapp_number' => '+947'.fake()->unique()->numerify('########'),
            'gn_division' => fake()->randomElement(['Buttala', 'Okkampitiya', 'Kumbukkana', 'Pelwatta', 'Yudaganawa']),
        ];
    }
}
