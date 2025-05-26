<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\ConditionsController;
use App\Models\Condition;
use App\Models\Module;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionsControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_exceedsCategoryLimit_false_when_no_existing_conditions()
    {
        $controller = new ConditionsController();
        $module = Module::factory()->create(['category' => 'Example']);
        $slot = Slot::factory()->create();

        $this->assertFalse($controller->exceedsCategoryLimit($module, $slot));
    }

    public function test_exceedsCategoryLimit_true_when_limit_exceeded()
    {
        Condition::factory()->create(['category' => 'Limited', 'max' => 1]);
        Module::factory()->create(['category' => 'Limited']);
        $module = Module::factory()->create(['category' => 'Limited']);
        $slot = Slot::factory()->create();

        Slot::factory()->create([
            'module_id' => Module::factory()->create(['category' => 'Limited'])->id
        ]);

        $controller = new ConditionsController();
        $this->assertTrue($controller->exceedsCategoryLimit($module, $slot));
    }

    public function test_violatesAdjacencyRule_false_when_no_incompatible_categories()
    {
        Condition::factory()->create(['category' => 'Test', 'incompatible' => []]);
        $module = Module::factory()->create(['category' => 'Test']);
        $slot = Slot::factory()->create(['index' => 0]);

        $controller = new ConditionsController();
        $this->assertFalse($controller->violatesAdjacencyRule($module, $slot));
    }

    public function test_violatesAdjacencyRule_true_with_incompatible_adjacent()
    {
        Condition::factory()->create(['category' => 'Test', 'incompatible' => ['Bad']]);
        $module = Module::factory()->create(['category' => 'Test']);
        $slot = Slot::factory()->create(['index' => 0]);

        Slot::factory()->create([
            'index' => 1,
            'module_id' => Module::factory()->create(['category' => 'Bad'])->id
        ]);

        $controller = new ConditionsController();
        $this->assertTrue($controller->violatesAdjacencyRule($module, $slot));
    }
}
