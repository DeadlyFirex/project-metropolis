<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Slot;
use App\Models\Module;
use App\Models\Event;

/**
 * Controller voor het afhandelen van gebruikersfeedback.
 * Bevat methodes voor weergeven, opslaan, bijwerken en verwijderen van feedback.
 */
class FeedbackController extends Controller
{
    /**
     * Toon alle feedback (volledige pagina of alleen de lijst bij AJAX).
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index(Request $request)
    {
        $feedback = Feedback::latest()->get();

        // Indien AJAX, laad alleen de component met de lijst
        if ($request->ajax()) {
            return view('components.feedback.list', compact('feedback'));
        }

        // Volledige pagina: inclusief data voor simulatie-dashboard
        $slots = Slot::with(['module.effects'])->get();
        $modules = Module::with('effects')->get();
        $categories = Module::select('category')->distinct()->pluck('category');
        $events = Event::all();
        $clockTime = '00:00:00'; // Kan worden aangepast met klokdata per gebruiker

        return view('sim_dashboard', compact(
            'slots',
            'modules',
            'categories',
            'events',
            'clockTime',
            'feedback'
        ));
    }

    /**
     * Werk bestaande feedback bij.
     *
     * @param  Request   $request
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function update(Request $request, Feedback $feedback)
    {
        // Haal data op van JSON of normale POST
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $validated = validator($data, [
            'content' => 'required|string|max:2000',
        ])->validate();

        $feedback->update(['content' => $validated['content']]);

        // AJAX: vernieuw enkel de feedbacklijst-component
        if ($request->ajax()) {
            $feedback = Feedback::latest()->get();
            return view('components.feedback.list', compact('feedback'));
        }

        return redirect()->route('feedback.index')->with('success', 'Feedback bijgewerkt.');
    }

    /**
     * Verwijder een feedback-item.
     *
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Feedback verwijderd.');
    }

    /**
     * Sla nieuwe feedback op.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        Feedback::create([
            'content' => $request->input('content'),
        ]);

        // AJAX: enkel lijst-component teruggeven
        if ($request->ajax()) {
            $feedback = Feedback::latest()->get();
            return view('components.feedback.list', compact('feedback'));
        }

        return back()->with('success', 'Bedankt voor je feedback!');
    }
}
