<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'status'  => 'required|in:office,remote',
        ]);

        // Admin setting a permanent location for another employee
        if (!empty($validated['user_id']) && Auth::user()->isManager()) {
            User::where('id', $validated['user_id'])
                ->update(['work_location' => $validated['status']]);

            return response()->json(['ok' => true]);
        }

        // Employee checking in for themselves (daily attendance)
        DailyCheckin::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => today()->toDateString()],
            ['status' => $validated['status'], 'checked_in_at' => now()]
        );

        return back()->with('checkin_updated', true);
    }
}
