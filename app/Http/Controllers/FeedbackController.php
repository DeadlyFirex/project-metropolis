<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
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

