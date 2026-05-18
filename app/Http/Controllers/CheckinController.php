<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    // Employee checks in for the day
    public function store(Request $request)
    {
        DailyCheckin::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => today()->toDateString()],
            ['checked_in_at' => now(), 'signed_out_at' => null]
        );

        return back();
    }

    // Employee checks out for the day
    public function checkout(Request $request)
    {
        DailyCheckin::where('user_id', Auth::id())
            ->where('date', today()->toDateString())
            ->update(['signed_out_at' => now()]);

        return back();
    }

    // Admin: correct a checkin record's times
    public function update(Request $request, DailyCheckin $checkin)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'checked_in_at' => 'required|date_format:H:i',
            'signed_out_at' => 'nullable|date_format:H:i|after:checked_in_at',
        ]);

        $date = $checkin->date->toDateString();

        $checkin->update([
            'checked_in_at' => Carbon::parse($date . ' ' . $validated['checked_in_at']),
            'signed_out_at' => $validated['signed_out_at']
                ? Carbon::parse($date . ' ' . $validated['signed_out_at'])
                : null,
        ]);

        return back()->with('success', 'Attendance record updated.');
    }
}
