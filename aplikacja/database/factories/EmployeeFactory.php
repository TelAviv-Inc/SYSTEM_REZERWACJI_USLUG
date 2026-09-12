<?php

namespace Database\Factories;

use App\Models\employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            
            'uuid' => fake()->uuid(),
            'user_id' => User::where('role', 'employee')->inRandomOrder()->first()->uuid,
            'description' => fake()->text(200),
            'active' => 1
            
        ];
    }
}
