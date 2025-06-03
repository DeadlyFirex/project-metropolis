<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = $this->getAvailableEvents();
        return view('event_dashboard', compact('events'));
    }

    public function setEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'duration' => 'nullable|integer|min:1',
            'duration_unit' => 'nullable|string|in:minutes,hours,days',
            'is_recurring' => 'boolean',
            'recurring_interval' => 'nullable|integer|min:1',
            'recurring_unit' => 'nullable|string|in:minutes,hours,days'
        ]);

        // Store event data in session or database
        session([
            'active_event' => [
                'type' => $request->event_type,
                'duration' => $request->duration,
                'duration_unit' => $request->duration_unit,
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_interval' => $request->recurring_interval,
                'recurring_unit' => $request->recurring_unit,
                'started_at' => now()
            ]
        ]);

        return redirect()->back()->with('success', 'Event is succesvol ingesteld!');
    }

    public function resetEvent()
    {
        session()->forget('active_event');
        return redirect()->back()->with('success', 'Omstandigheden zijn teruggezet naar normaal!');
    }

    private function getAvailableEvents()
    {
        return [
            'festival' => 'Festival',
            'rush_hour' => 'Spitsuur'
        ];
    }
}
