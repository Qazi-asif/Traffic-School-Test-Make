<?php

namespace Database\Factories;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateTransmissionFactory extends Factory
{
    protected $model = StateTransmission::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => UserCourseEnrollment::factory(),
            'state' => 'FL',
            'status' => 'pending',
            'payload_json' => null,
            'response_code' => null,
            'response_message' => null,
            'sent_at' => null,
            'retry_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'sent_at' => now(),
            'response_code' => '200',
            'response_message' => 'Successfully transmitted',
        ]);
    }

    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'response_code' => '400',
            'response_message' => 'Validation failed',
            'retry_count' => fake()->numberBetween(1, 3),
        ]);
    }
}
