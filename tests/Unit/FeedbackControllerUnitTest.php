<?php

namespace Tests\Unit;

use App\Http\Controllers\FeedbackController;
use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * This test class directly tests the logic inside the FeedbackController methods.
 * It bypasses route/middleware/auth layers and focuses only on database effects.
 */
class FeedbackControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the controller's store method can successfully
     * create a new feedback entry in the database.
     */
    public function test_it_can_store_feedback_directly_via_controller()
    {
        $controller = new FeedbackController();

        $request = Request::create('/feedback', 'POST', [
            'content' => 'Testfeedback via controller',
        ]);

        $controller->store($request);

        $this->assertDatabaseHas('feedback', [
            'content' => 'Testfeedback via controller',
        ]);
    }

    /**
     * Test that the controller's update method correctly updates
     * the content of an existing feedback record.
     */
    public function test_it_can_update_feedback_via_controller()
    {
        $controller = new FeedbackController();

        $feedback = Feedback::create(['content' => 'Oude content']);

        $request = Request::create("/feedback/{$feedback->id}", 'PATCH', [
            'content' => 'Nieuwe content',
        ]);

        $controller->update($request, $feedback);

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'content' => 'Nieuwe content',
        ]);
    }

    /**
     * Test that the controller's destroy method removes
     * a feedback record from the database.
     */
    public function test_it_can_delete_feedback_via_controller()
    {
        $controller = new FeedbackController();

        $feedback = Feedback::create(['content' => 'Te verwijderen']);

        $controller->destroy($feedback);

        $this->assertDatabaseMissing('feedback', [
            'id' => $feedback->id,
        ]);
    }
}
