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
 * Feature tests for SimulationController.
 *
 * Verifies that the koppelModule endpoint properly assigns modules to slots or returns validation errors,
 * and that the category parameter is passed through to the view.
 */
class SimulationControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/simulation', [SimulationController::class, 'index']);
        Route::post('/simulation/koppelModule', [SimulationController::class, 'koppelModule']);

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Test that the category parameter is received from the request
     * and provided to the view.
     */
    public function testCategoryParameterIsPassedToView()
    {
        Module::factory()->create(['category' => 'catA']);

        $response = $this->get('/simulation?category=catA');

        $response->assertStatus(200);
        $this->assertEquals('catA', $response->viewData('category'));
    }

    /**
     * Test that posting valid module_id and slot_id to koppelModule
     * assigns the module to the slot and returns success JSON.
     */
    public function testKoppelModuleValidRequestAssignsModuleToSlot()
    {
        $module = Module::factory()->create();
        $slot = Slot::factory()->create();

        $response = $this->postJson('/simulation/koppelModule', [
            'module_id' => $module->id,
            'slot_id'   => $slot->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('slots', [
            'id'        => $slot->id,
            'module_id' => $module->id,
        ]);
    }

    /**
     * Test that posting invalid module_id and slot_id to koppelModule
     * returns validation errors for both fields.
     */
    public function testKoppelModuleInvalidDataReturnsValidationErrors()
    {
        $response = $this->postJson('/simulation/koppelModule', [
            'module_id' => 999,
            'slot_id'   => 999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['module_id', 'slot_id']);
    }
}
