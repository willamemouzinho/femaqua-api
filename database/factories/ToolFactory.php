<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ToolFactory extends Factory
{
    public function definition() : array
    {
        return [
            'title' => fake()->word(),
            'link' => fake()->url(),
            'description' => fake()->text()
        ];
    }
}
