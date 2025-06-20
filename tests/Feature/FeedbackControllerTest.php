<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * This test suite covers the main functionality of the FeedbackController.
 * It verifies that feedback can be listed, created, validated, updated and deleted correctly.
 */
class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the feedback index page loads successfully and includes feedback items in the view.
     */
    #[Test]
    public function it_shows_the_feedback_index_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Feedback::factory()->count(2)->create();

        $response = $this->get('/feedback');

        $response->assertStatus(200);
        $response->assertSee('Feedbackoverzicht');
        $response->assertViewHas('feedback');
    }

    /**
     * Test that a new feedback item can be stored in the database.
     */
    #[Test]
    public function it_can_store_feedback()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/feedback', [
            'content' => 'Dit is een testfeedback.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feedback', [
            'content' => 'Dit is een testfeedback.',
        ]);
    }

    /**
     * Test that the controller correctly validates content length (max 2000 chars).
     */
    #[Test]
    public function it_shows_validation_error_if_feedback_content_is_too_long()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $longContent = str_repeat('a', 2001);

        $response = $this->post('/feedback', [
            'content' => $longContent,
        ]);

        $response->assertSessionHasErrors('content');
    }

    /**
     * Test that an existing feedback item can be updated successfully.
     */
    #[Test]
    public function it_can_update_feedback()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $feedback = Feedback::factory()->create([
            'content' => 'Originele content',
        ]);

        $response = $this->patch("/feedback/{$feedback->id}", [
            'content' => 'Aangepaste feedback',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'content' => 'Aangepaste feedback',
        ]);
    }

    /**
     * Test that a feedback item can be deleted and no longer exists in the database.
     */
    #[Test]
    public function it_can_delete_feedback()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $feedback = Feedback::factory()->create();

        $response = $this->delete("/feedback/{$feedback->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('feedback', [
            'id' => $feedback->id,
        ]);
    }
}
