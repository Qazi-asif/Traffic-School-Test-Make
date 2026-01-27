<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'duration' => $this->faker->numberBetween(15, 60), // 15-60 minutes
            'order' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}