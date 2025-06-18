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
}
