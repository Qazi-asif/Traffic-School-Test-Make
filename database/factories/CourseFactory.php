<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'duration' => $this->faker->numberBetween(60, 480), // 1-8 hours
            'price' => $this->faker->randomFloat(2, 29.99, 199.99),
            'state_code' => $this->faker->randomElement(['FL', 'CA', 'TX', 'NY']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}