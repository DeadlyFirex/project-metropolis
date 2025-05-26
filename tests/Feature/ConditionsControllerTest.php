<?php

namespace Tests\Feature;

use App\Models\Condition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_display_the_conditions_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('conditions'));

        $response->assertOk()
            ->assertViewIs('conditions_dashboard');
    }

    /** @test */
    public function it_can_store_a_new_condition()
    {
        $data = [
            'category' => 'Test Category',
            'max' => 5,
            'incompatible' => ['Other Category']
        ];

        $response = $this->actingAs($this->user)
            ->post(route('conditions.store'), $data);

        $response->assertRedirect()
            ->assertSessionHas('status', 'Categorie toegevoegd');

        $this->assertDatabaseHas('conditions', [
            'category' => 'Test Category',
            'max' => 5
        ]);
    }

    /** @test */
    public function it_can_update_an_existing_condition()
    {
        $condition = Condition::create([
            'category' => 'Old Category',
            'max' => 2,
            'incompatible' => []
        ]);

        $data = [
            'max' => 10,
            'incompatible' => ['Updated Category']
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('conditions.update', $condition), $data);

        $response->assertRedirect()
            ->assertSessionHas('status', 'Regel aangepast');

        $this->assertDatabaseHas('conditions', [
            'id' => $condition->id,
            'max' => 10,
            'incompatible' => json_encode(['Updated Category'])
        ]);
    }

    /** @test */
    public function it_can_delete_a_condition()
    {
        $condition = Condition::create([
            'category' => 'Delete Category',
            'max' => 3,
            'incompatible' => []
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('conditions.destroy', $condition));

        $response->assertRedirect()
            ->assertSessionHas('status', 'Categorie verwijderd');

        $this->assertDatabaseMissing('conditions', [
            'id' => $condition->id
        ]);
    }
}
