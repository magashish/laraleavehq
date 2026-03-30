<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:office,remote',
        ]);

        DailyCheckin::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => today()->toDateString()],
            ['status' => $validated['status'], 'checked_in_at' => now()]
        );

        return back()->with('checkin_updated', true);
    }
}
