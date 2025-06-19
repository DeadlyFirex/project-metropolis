<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserClock;

class ClockController extends Controller
{
    public function store(Request $request)
    {
        $time = $request->input('time');

        UserClock::updateOrCreate(
            ['user_id' => auth()->id()],
            ['clock_time' => $time]
        );

        return response()->json(['status' => 'ok']);
    }

    public function current(Request $request)
    {
        $time = UserClock::where('user_id', $request->user()->id)
            ->value('clock_time')          // 'HH:MM:SS' óf null
            ?? now()->format('H:i:s');        // fallback indien null

        return response($time, 200)
            ->header('Content-Type', 'text/plain');
    }
}
