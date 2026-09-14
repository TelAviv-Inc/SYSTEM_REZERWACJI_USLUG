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
    protected static int $index = 0;
    public function definition(): array
    {
        
        $kategorie = ['Koloryzacja', 'Ciecie', 'Zabiegi', 'Pielegnacja'];
        $opisy = [
            'Farbowanie, balayage, refleksy i inne techniki zmiany koloru włosów.',
            'Strzyżenie damskie, męskie i dziecięce dopasowane do kształtu twarzy.',
            'Regeneracja, botoks do włosów i inne zabiegi specjalistyczne.',
            'Stylizacja, maski i produkty do codziennej pielęgnacji włosów.',
        ];

        $ikony = [
            'fa-solid fa-palette',
            'fa-solid fa-scissors',
            'fa-solid fa-spa',
            'fa-solid fa-pump-medical'
        ];

        $index = static::$index % count($kategorie);
        static::$index++;
        return [
            'uuid' => fake()->uuid(),
            'name' => $kategorie[$index],
            'description' => $opisy[$index],
            'icon' => $ikony[$index]
            
        ];
    }
}
