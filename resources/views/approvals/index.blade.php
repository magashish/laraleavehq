<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pending Approvals</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @forelse($requests as $request)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $request->leaveType->color }}"></span>
                                    <h3 class="font-medium text-gray-900">{{ $request->user->name }}</h3>
                                    <span class="text-sm text-gray-500">&mdash; {{ $request->leaveType->name }}</span>
                                </div>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>
                                        <span class="font-medium">Period:</span>
                                        {{ $request->start_date->format('M d, Y') }} &ndash; {{ $request->end_date->format('M d, Y') }}
                                        ({{ $request->total_days }} working day(s))
                                    </p>
                                    @if($request->user->department)
                                        <p><span class="font-medium">Department:</span> {{ $request->user->department }}</p>
                                    @endif
                                    <p><span class="font-medium">Reason:</span> {{ $request->reason }}</p>
                                    <p class="text-xs text-gray-400">Submitted {{ $request->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="flex gap-2 ml-4 shrink-0">
                                <!-- Approve -->
                                <form method="POST" action="{{ route('approvals.approve', $request) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                                        Approve
                                    </button>
                                </form>

                                <!-- Reject with comment -->
                                <button onclick="document.getElementById('reject-form-{{ $request->id }}').classList.toggle('hidden')"
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                                    Reject
                                </button>
                            </div>
                        </div>

                        <!-- Reject Form (hidden by default) -->
                        <div id="reject-form-{{ $request->id }}" class="hidden mt-4 pt-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('approvals.reject', $request) }}">
                                @csrf @method('PATCH')
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason (required)</label>
                                    <textarea name="reviewer_comment" rows="2" required
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500"
                                        placeholder="Please provide a reason for rejection..."></textarea>
                                </div>
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                                    Confirm Rejection
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <p class="text-gray-500">No pending approvals. All caught up!</p>
                    </div>
                @endforelse

                @if($requests->hasPages())
                    <div>{{ $requests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
