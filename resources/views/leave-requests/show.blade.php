<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leave Request Details</h2>
            <a href="{{ route('leave-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Status Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full" style="background-color: {{ $leaveRequest->leaveType->color }}"></span>
                        <h3 class="font-medium text-gray-900">{{ $leaveRequest->leaveType->name }}</h3>
                    </div>
                    <span class="text-sm px-3 py-1 rounded-full font-medium
                        @if($leaveRequest->status === 'approved') bg-green-100 text-green-700
                        @elseif($leaveRequest->status === 'rejected') bg-red-100 text-red-700
                        @elseif($leaveRequest->status === 'cancelled') bg-gray-100 text-gray-700
                        @else bg-yellow-100 text-yellow-700 @endif">
                        {{ ucfirst($leaveRequest->status) }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Employee</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Total Days</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->total_days }} working day(s)</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Start Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">End Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->end_date->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Submitted On</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Reason</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if($leaveRequest->reviewed_at)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-medium text-gray-500 uppercase">Reviewed By</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reviewer->name ?? 'N/A' }} on {{ $leaveRequest->reviewed_at->format('F j, Y') }}</p>
                            @if($leaveRequest->reviewer_comment)
                                <p class="text-xs font-medium text-gray-500 uppercase mt-3">Reviewer Comment</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reviewer_comment }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if($leaveRequest->isPending() && $leaveRequest->user_id === Auth::id())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <form method="POST" action="{{ route('leave-requests.cancel', $leaveRequest) }}"
                            onsubmit="return confirm('Are you sure you want to cancel this request?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                                Cancel Request
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
