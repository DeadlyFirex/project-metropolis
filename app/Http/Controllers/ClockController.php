<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clock;

class ClockController extends Controller
{
    /**
     * Save the clock time for the authenticated user.
     * This method is an endpoint to update the user's clock time.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request to ensure 'time' is in the format HH:MM:SS
        $request->validate([
            'time' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $time = $request->input('time');
        $date = $request->input('date');

        Clock::updateOrCreate(
            ['user_id' => $userId],
            ['time' => $time, 'date' => $date],
        );

        return response()->json(['success' => true]);
    }

    /**
     * Get the current clock time & date for the authenticated user.
     * This method is an endpoint to retrieve the user's clock time.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request): \Illuminate\Http\JsonResponse
    {
        $time = Clock::where('user_id', $request->user()->id)
            ->value('time')          // 'HH:MM:SS' or null
            ?? now()->format('H:i:s');

        $date = Clock::where('user_id', $request->user()->id)
            ->value('date')          // 'YYYY-MM-DD' or null
            ?? now()->format('Y-m-d');

        return response()->json([
            'time' => $time,
            'date' => $date,
        ]);
    }
}
