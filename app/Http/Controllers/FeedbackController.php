<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Slot;
use App\Models\Module;
use App\Models\Event;


class FeedbackController extends Controller
{

    public function index()
    {
        $feedback = Feedback::latest()->get();
        $slots = Slot::with(['module.effects'])->get();
        $modules = Module::with('effects')->get();
        $categories = Module::select('category')->distinct()->pluck('category');
        $events = Event::all();
        $clockTime = '00:00:00'; // of ophalen via UserClock als je dat wil

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
        $request->validate(['content' => 'required|string|max:2000']);
        $feedback->update(['content' => $request->content]);
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

        return back()->with('success', 'Bedankt voor je feedback!');
    }
}
