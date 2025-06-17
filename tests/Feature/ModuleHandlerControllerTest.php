<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleHandlerControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_a_module()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $module = Module::factory()->make();

        $response = $this->post(route('modules.store'), [
            'name' => $module->name,
            'description' => $module->description,
            'category' => $module->category,
            'image' => null,
        ]);

        $response->assertRedirect(route('module.index'));

        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
            'description' => $module->description,
            'category' => $module->category,
            'image_path' => 'default-image.png',
        ]);
    }

    /** @test */
    public function it_can_update_a_module()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $module = Module::factory()->create([
            'category' => 'Recreatie',
            'image_path' => 'default-image.png',
        ]);

        $newData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'category' => 'Veiligheid',
        ];

        $response = $this->put(route('modules.update', $module->id), $newData);

        $response->assertRedirect(route('module.index'));

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'category' => 'Veiligheid',
            'image_path' => 'default-image.png',
        ]);
    }

    /** @test */
    public function it_soft_deletes_a_module()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $module = Module::factory()->create();

        $response = $this->delete(route('modules.destroy', $module->id));

        $response->assertRedirect(route('module.index'));

        // Check that the module is soft deleted
        $this->assertSoftDeleted('modules', ['id' => $module->id]);
    }

    /** @test */
    public function it_removes_module_from_active_list_but_not_from_database()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $module = Module::factory()->create();

        $this->delete(route('modules.destroy', $module->id));

        // Check that the module is not returned by default queries
        $this->assertFalse(Module::all()->contains($module));

        // But it still exists in the database (soft deleted)
        $this->assertSoftDeleted('modules', ['id' => $module->id]);
    }
}
