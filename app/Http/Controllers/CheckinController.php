<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->isManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status'  => 'required|in:office,remote',
        ]);

        DailyCheckin::updateOrCreate(
            ['user_id' => $validated['user_id'], 'date' => today()->toDateString()],
            ['status' => $validated['status'], 'checked_in_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}
