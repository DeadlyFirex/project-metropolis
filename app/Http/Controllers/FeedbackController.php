<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Slot;
use App\Models\Module;
use App\Models\Event;


class FeedbackController extends Controller
{

    public function index(Request $request)
    {
        $feedback = Feedback::latest()->get();

        // AJAX? Geef alleen feedbacklijst terug
        if ($request->ajax()) {
            return view('components.feedback.list', compact('feedback'));
        }

        // Volledige pagina
        $slots = Slot::with(['module.effects'])->get();
        $modules = Module::with('effects')->get();
        $categories = Module::select('category')->distinct()->pluck('category');
        $events = Event::all();
        $clockTime = '00:00:00'; // eventueel via UserClock ophalen

        return view('sim_dashboard', compact(
            'slots',
            'modules',
            'categories',
            'events',
            'clockTime',
            'feedback'
        ));
    }


    public function update(Request $request, Feedback $feedback)
{
    $data = $request->isJson() ? $request->json()->all() : $request->all();

    $validated = validator($data, [
        'content' => 'required|string|max:2000',
    ])->validate();

    $feedback->update(['content' => $validated['content']]);

    if ($request->ajax()) {
        $feedback = Feedback::latest()->get();
        return view('components.feedback.list', compact('feedback'));
    }

    return redirect()->route('feedback.index')->with('success', 'Feedback bijgewerkt.');
}

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Feedback verwijderd.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        Feedback::create([
            'content' => $request->input('content'),
        ]);

        if ($request->ajax()) {
            // geef alleen de lijst-partial terug
            $feedback = Feedback::latest()->get();
            return view('components.feedback.list', compact('feedback'));
        }

        return back()->with('success', 'Bedankt voor je feedback!');
    }
}
