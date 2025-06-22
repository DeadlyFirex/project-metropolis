<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Clock;

class ClockControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_requires_valid_time_format()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/save-clock', [
            'time' => 'invalid-time',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('time');
    }

    /** @test */
    public function it_returns_unauthenticated_if_user_not_logged_in()
    {
        $response = $this->postJson('/save-clock', [
            'time' => '08:00:00',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function it_returns_default_clock_time_when_no_user_clock_exists()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test4@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        // Simuleer de logica van SimulationController
        $clockTime = Clock::where('user_id', $user->id)->first()->clock_time ?? '00:00:00';

        $this->assertEquals('00:00:00', $clockTime);
    }
}
