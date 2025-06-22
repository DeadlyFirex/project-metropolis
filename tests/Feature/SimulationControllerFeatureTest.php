<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimulationController;
use App\Models\User;
use App\Models\Module;
use App\Models\Slot;

/**
 * Feature-tests voor SimulationController.
 */
class SimulationControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/simulation', [SimulationController::class, 'index']);
        Route::post('/simulation/koppelModule', [SimulationController::class, 'koppelModule']);

        $this->actingAs(User::factory()->create());
    }


    public function testKoppelModuleInvalidDataReturnsValidationErrors(): void
    {
        $response = $this->postJson('/simulation/koppelModule', [
            'module_id' => 999,
            'slot_id'   => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }
}
