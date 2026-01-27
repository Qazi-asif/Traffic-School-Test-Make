<?php

namespace Tests\Unit;

use App\Jobs\SendFloridaTransmissionJob;
use App\Models\FloridaCourse;
use App\Models\StateTransmission;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StateTransmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed error codes
        $this->artisan('db:seed', ['--class' => 'TransmissionErrorCodeSeeder']);
    }

    public function test_transmission_can_be_created()
    {
        $user = User::factory()->create();
        $course = FloridaCourse::factory()->create();
        $enrollment = UserCourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        $transmission = StateTransmission::create([
            'enrollment_id' => $enrollment->id,
            'state' => 'FL',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('state_transmissions', [
            'enrollment_id' => $enrollment->id,
            'state' => 'FL',
            'status' => 'pending',
        ]);
    }

    public function test_successful_transmission_updates_status()
    {
        Http::fake([
            '*' => Http::response(['success' => true, 'message' => 'Transmitted'], 200),
        ]);

        $transmission = $this->createTestTransmission();

        $job = new SendFloridaTransmissionJob($transmission->id);
        $job->handle();

        $transmission->refresh();

        $this->assertEquals('success', $transmission->status);
        $this->assertNotNull($transmission->sent_at);
        $this->assertEquals('200', $transmission->response_code);
    }

    public function test_failed_transmission_updates_status()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Validation failed'], 422),
        ]);

        $transmission = $this->createTestTransmission();

        try {
            $job = new SendFloridaTransmissionJob($transmission->id);
            $job->handle();
        } catch (\Exception $e) {
            // Expected to throw for retry
        }

        $transmission->refresh();

        $this->assertEquals('error', $transmission->status);
        $this->assertEquals('422', $transmission->response_code);
        $this->assertGreaterThan(0, $transmission->retry_count);
    }

    public function test_validation_error_marks_as_error()
    {
        $user = User::factory()->create([
            'driver_license_number' => null, // Missing required field
        ]);

        $enrollment = UserCourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'completed_at' => now(),
        ]);

        $transmission = StateTransmission::create([
            'enrollment_id' => $enrollment->id,
            'state' => 'FL',
            'status' => 'pending',
        ]);

        $job = new SendFloridaTransmissionJob($transmission->id);
        $job->handle();

        $transmission->refresh();

        $this->assertEquals('error', $transmission->status);
        $this->assertEquals('VALIDATION_ERROR', $transmission->response_code);
    }

    public function test_pending_scope_returns_only_pending()
    {
        StateTransmission::factory()->pending()->count(3)->create();
        StateTransmission::factory()->success()->count(2)->create();
        StateTransmission::factory()->error()->count(1)->create();

        $pending = StateTransmission::pending()->get();

        $this->assertCount(3, $pending);
        $this->assertTrue($pending->every(fn ($t) => $t->status === 'pending'));
    }

    public function test_error_scope_returns_only_errors()
    {
        StateTransmission::factory()->pending()->count(3)->create();
        StateTransmission::factory()->success()->count(2)->create();
        StateTransmission::factory()->error()->count(1)->create();

        $errors = StateTransmission::error()->get();

        $this->assertCount(1, $errors);
        $this->assertTrue($errors->every(fn ($t) => $t->status === 'error'));
    }

    public function test_for_state_scope_filters_by_state()
    {
        StateTransmission::factory()->create(['state' => 'FL']);
        StateTransmission::factory()->create(['state' => 'FL']);
        StateTransmission::factory()->create(['state' => 'MO']);

        $florida = StateTransmission::forState('FL')->get();

        $this->assertCount(2, $florida);
        $this->assertTrue($florida->every(fn ($t) => $t->state === 'FL'));
    }

    protected function createTestTransmission(): StateTransmission
    {
        $user = User::factory()->create([
            'driver_license_number' => 'A123456789012',
            'court_case_number' => 'CASE123',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $course = FloridaCourse::factory()->create();

        $enrollment = UserCourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'citation_number' => 'CIT123456',
            'completed_at' => now(),
        ]);

        return StateTransmission::create([
            'enrollment_id' => $enrollment->id,
            'state' => 'FL',
            'status' => 'pending',
        ]);
    }
}
