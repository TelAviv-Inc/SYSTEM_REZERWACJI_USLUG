<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */

class ServicesFactory extends Factory
{
    protected static int $index = 0;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ServiceCategory::all('uuid');
        $index = static::$index % count($categories);
        return [
            'uuid' => fake()->uuid(),
            'category_id' => $categories[$index],
            'name' => fake()->text(20),
            'description' => fake()->text(200),
            'duration' => fake()->numberBetween(30, 120),
            'price' => fake()->numberBetween(80,320),
            'active' => fake()->boolean(75)
        ];
    }
}
