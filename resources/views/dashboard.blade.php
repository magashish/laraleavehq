<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Requests</div>
                    <div class="mt-1 text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Approved This Year</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ $stats['approved_this_year'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Days Taken This Year</div>
                    <div class="mt-1 text-3xl font-bold text-indigo-600">{{ $stats['total_days_taken'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Leave Balances -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Leave Balances ({{ now()->year }})</h3>
                        <a href="{{ route('leave-requests.create') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Request Leave
                        </a>
                    </div>
                    <div class="p-6">
                        @forelse($balances as $balance)
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full inline-block" style="background-color: {{ $balance->leaveType->color }}"></span>
                                        <span class="text-sm font-medium text-gray-700">{{ $balance->leaveType->name }}</span>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        {{ $balance->remaining_days }} / {{ $balance->allocated_days }} days left
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $pct = $balance->allocated_days > 0
                                            ? min(100, (($balance->used_days + $balance->pending_days) / $balance->allocated_days) * 100)
                                            : 0;
                                    @endphp
                                    <div class="h-2 rounded-full" style="width: {{ $pct }}%; background-color: {{ $balance->leaveType->color }}"></div>
                                </div>
                                @if($balance->pending_days > 0)
                                    <p class="text-xs text-yellow-600 mt-1">{{ $balance->pending_days }} days pending approval</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No leave balances allocated yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Requests -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Recent Requests</h3>
                        <a href="{{ route('leave-requests.index') }}" class="text-sm text-indigo-600 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentRequests as $request)
                            <a href="{{ route('leave-requests.show', $request) }}" class="block px-6 py-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $request->leaveType->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $request->start_date->format('M d') }} &ndash; {{ $request->end_date->format('M d, Y') }}
                                            &bull; {{ $request->total_days }} day(s)
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full font-medium
                                        @if($request->status === 'approved') bg-green-100 text-green-700
                                        @elseif($request->status === 'rejected') bg-red-100 text-red-700
                                        @elseif($request->status === 'cancelled') bg-gray-100 text-gray-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="px-6 py-4 text-gray-500 text-sm">No leave requests yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Approvals (Manager/Admin) -->
                @if(Auth::user()->isManager() && $pendingApprovals && $pendingApprovals->isNotEmpty())
                    <div class="bg-white rounded-lg shadow lg:col-span-2">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Pending Approvals</h3>
                            <a href="{{ route('approvals.index') }}" class="text-sm text-indigo-600 hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($pendingApprovals as $req)
                                <div class="px-6 py-4 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $req->user->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $req->leaveType->name }} &bull;
                                            {{ $req->start_date->format('M d') }} &ndash; {{ $req->end_date->format('M d, Y') }}
                                            &bull; {{ $req->total_days }} day(s)
                                        </p>
                                    </div>
                                    <a href="{{ route('approvals.index') }}" class="text-sm text-indigo-600 hover:underline">Review</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
