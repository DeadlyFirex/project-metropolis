<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $events = $this->getAvailableEvents();
        $slots = Slot::all();
        $activeEvents = $this->getActiveSlotEvents();

        return view('event_dashboard', compact('events', 'slots', 'activeEvents'));
    }

    public function setEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'slot_id' => 'required|exists:slots,id',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|string|in:minutes,hours,days',
            'is_recurring' => 'boolean',
            'recurring_interval' => 'nullable|integer|min:1',
            'recurring_unit' => 'nullable|string|in:minutes,hours,days'
        ]);

        // Calculate end time
        $startTime = now();
        // Cast duration to int
        $endTime = $this->calculateEndTime($startTime, (int)$request->duration, $request->duration_unit);

        // Store event data in session with slot-specific key
        $eventKey = 'slot_event_' . $request->slot_id;
        session([
            $eventKey => [
                'slot_id' => $request->slot_id,
                'type' => $request->event_type,
                'duration' => (int)$request->duration, // Cast to int
                'duration_unit' => $request->duration_unit,
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_interval' => $request->recurring_interval ? (int)$request->recurring_interval : null, // Cast to int if present
                'recurring_unit' => $request->recurring_unit,
                'started_at' => $startTime->toDateTimeString(),
                'ends_at' => $endTime->toDateTimeString()
            ]
        ]);

        return redirect()->back()->with('success', 'Event is succesvol ingesteld voor vakje ' . $request->slot_id . '!');
    }

    public function resetEvent(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:slots,id'
        ]);

        $eventKey = 'slot_event_' . $request->slot_id;
        session()->forget($eventKey);

        return redirect()->back()->with('success', 'Event voor vakje ' . $request->slot_id . ' is teruggezet naar normaal!');
    }

    public function getSlotEvents()
    {
        return response()->json($this->getActiveSlotEvents());
    }

    private function getActiveSlotEvents()
    {
        $activeEvents = [];
        $allSessionData = session()->all();

        foreach ($allSessionData as $key => $data) {
            if (strpos($key, 'slot_event_') === 0 && is_array($data)) {
                $slotId = $data['slot_id'] ?? null;
                if ($slotId) {
                    $endTime = Carbon::parse($data['ends_at']);
                    $now = now();

                    if ($endTime->isFuture()) {
                        $timeRemaining = $this->getTimeRemaining($now, $endTime);
                        $activeEvents[$slotId] = array_merge($data, [
                            'time_remaining' => $timeRemaining,
                            'event_name' => $this->getAvailableEvents()[$data['type']] ?? $data['type']
                        ]);
                    } else {
                        // Event expired, remove it
                        session()->forget($key);
                    }
                }
            }
        }

        return $activeEvents;
    }

    private function calculateEndTime($startTime, $duration, $unit)
    {
        // $duration is already cast to int in setEvent, but adding a defensive cast here
        // for robustness in case this method is called from elsewhere without validation
        switch ($unit) {
            case 'minutes':
                return $startTime->copy()->addMinutes((int)$duration);
            case 'hours':
                return $startTime->copy()->addHours((int)$duration);
            case 'days':
                return $startTime->copy()->addDays((int)$duration);
            default:
                return $startTime->copy()->addMinutes((int)$duration);
        }
    }

    private function getTimeRemaining($now, $endTime)
    {
        $diff = $now->diff($endTime);

        if ($diff->days > 0) {
            return $diff->days . ' dag(en), ' . $diff->h . ' uur';
        } elseif ($diff->h > 0) {
            return $diff->h . ' uur, ' . $diff->i . ' min';
        } else {
            return $diff->i . ' minuten';
        }
    }

    private function getAvailableEvents()
    {
        return [
            'festival' => 'Festival',
            'rush_hour' => 'Spitsuur',
            'construction' => 'Wegwerkzaamheden',
            'market_day' => 'Marktdag',
            'emergency' => 'Noodsituatie'
        ];
    }
}
