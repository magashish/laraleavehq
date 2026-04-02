<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'user_id'  => 'required|exists:users,id',
            'status'   => 'required|in:office,remote',
        ]);

        User::where('id', $validated['user_id'])
            ->update(['work_location' => $validated['status']]);

        return response()->json(['ok' => true]);
    }
}
