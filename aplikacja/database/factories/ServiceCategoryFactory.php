<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kategorie = ['Koloryzacja', 'Ciecie', 'Zabiegi', 'Pielegnacja'];
        return [
            'uuid' => fake()->uuid(),
            'name' => fake()->unique()->randomElement($kategorie),
            'description' => fake()->text(255)
            
        ];
    }
}
