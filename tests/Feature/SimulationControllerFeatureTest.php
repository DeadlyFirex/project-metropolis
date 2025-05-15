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

    /* ---------- bestaande tests ------------------------------------------------ */

    public function testCategoryParameterIsPassedToView(): void
    {
        Module::factory()->create(['category' => 'catA']);

        $response = $this->get('/simulation?category=catA');

        $response->assertStatus(200);
        $this->assertEquals('catA', $response->viewData('category'));
    }

    public function testKoppelModuleValidRequestAssignsModuleToSlot(): void
    {
        $module = Module::factory()->create();
        $slot   = Slot::factory()->create();

        $response = $this->postJson('/simulation/koppelModule', [
            'module_id' => $module->id,
            'slot_id'   => $slot->id,
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('slots', [
            'id'        => $slot->id,
            'module_id' => $module->id,
        ]);
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

    public function testCategoryLimitIsEnforced(): void
    {
        $slots = collect();
        for ($i = 1; $i <= 4; $i++) {
            $slots->push(Slot::factory()->create(['index' => $i]));
        }

        $modules = Module::factory()
            ->count(4)
            ->state(['category' => 'Residential'])
            ->create();

        foreach ($slots->take(3) as $i => $slot) {
            $this->postJson('/simulation/koppelModule', [
                'module_id' => $modules[$i]->id,
                'slot_id'   => $slot->id,
            ])->assertStatus(204);
        }

        $this->postJson('/simulation/koppelModule', [
            'module_id' => $modules[3]->id,
            'slot_id'   => $slots[3]->id,
        ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => __('errors.category_limit_reached', [
                    'limit'    => 3,
                    'category' => 'Residential',
                ]),
            ]);
    }

    public function testIncompatibleCategoriesAreRejected(): void
    {
        $slotA = Slot::factory()->create(['index' => 10]);
        $slotB = Slot::factory()->create(['index' => 11]);

        $care   = Module::factory()->state(['category' => 'Care'])->create();
        $public = Module::factory()->state(['category' => 'Public Space'])->create();

        $this->postJson('/simulation/koppelModule', [
            'module_id' => $care->id,
            'slot_id'   => $slotA->id,
        ])->assertStatus(204);

        $this->postJson('/simulation/koppelModule', [
            'module_id' => $public->id,
            'slot_id'   => $slotB->id,
        ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => __('errors.category_incompatible'),
            ]);
    }
}
