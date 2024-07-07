<?php

namespace Database\Factories;

use App\Enums\Uf;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'uf' => $this->faker->randomElement(Uf::cases())->value,
            'cnpj' => $this->faker->numerify('##############'),
        ];
    }
}
