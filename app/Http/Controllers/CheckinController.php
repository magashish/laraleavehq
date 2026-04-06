<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    // Employee checks in for the day
    public function store(Request $request)
    {
        // Admin setting persistent work_location for another employee (team page buttons)
        if ($request->has('user_id') && Auth::user()->isManager()) {
            $request->validate(['user_id' => 'required|exists:users,id', 'status' => 'required|in:office,remote']);
            User::where('id', $request->user_id)->update(['work_location' => $request->status]);
            return response()->json(['ok' => true]);
        }

        // Employee signing in for today
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
}
