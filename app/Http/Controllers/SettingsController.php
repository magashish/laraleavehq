<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        if (!Auth::user()->is_manager) {
            abort(403);
        }

        $employees = User::orderBy('name')
            ->withCount([
                'leaveRequests as used_days' => fn($q) => $q->where('status', 'approved')->select(\DB::raw('sum(days)')),
            ])
            ->get();

        $bankHolidays = BankHoliday::orderBy('date')->get();

        return view('settings.index', compact('employees', 'bankHolidays'));
    }

    public function addEmployee(Request $request)
    {
        if (!Auth::user()->is_manager) abort(403);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'role'         => 'required|string|max:100',
            'is_manager'   => 'boolean',
            'days_allowed' => 'required|integer|min:1|max:60',
            'color'        => 'required|string|max:10',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make('password123'),
            'role'         => $validated['role'],
            'is_manager'   => $request->boolean('is_manager'),
            'days_allowed' => $validated['days_allowed'],
            'color'        => $validated['color'],
        ]);

        return back()->with('success', "{$user->name} added. Default password: password123");
    }

    public function removeEmployee(User $user)
    {
        if (!Auth::user()->is_manager) abort(403);

        $user->delete();

        return back()->with('success', "{$user->name} removed.");
    }

    public function updateDays(Request $request, User $user)
    {
        if (!Auth::user()->is_manager) abort(403);

        $validated = $request->validate([
            'days_allowed' => 'required|integer|min:1|max:60',
        ]);

        $user->update(['days_allowed' => $validated['days_allowed']]);

        return back()->with('success', 'Allowance updated.');
    }

    public function addBankHoliday(Request $request)
    {
        if (!Auth::user()->is_manager) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'date' => 'required|date|unique:bank_holidays,date',
        ]);

        BankHoliday::create($validated);

        return back()->with('success', 'Bank holiday added.');
    }

    public function removeBankHoliday(BankHoliday $bankHoliday)
    {
        if (!Auth::user()->is_manager) abort(403);

        $bankHoliday->delete();

        return back()->with('success', "{$bankHoliday->name} removed.");
    }
}
