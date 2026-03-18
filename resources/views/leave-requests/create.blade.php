<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request Leave</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('leave-requests.store') }}">
                    @csrf

                    <!-- Leave Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                        <select name="leave_type_id" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select leave type...</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                    @if(isset($balances[$type->id]))
                                        ({{ $balances[$type->id]->remaining_days }} days available)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}"
                                min="{{ now()->toDateString() }}" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}"
                                min="{{ now()->toDateString() }}" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <textarea name="reason" rows="4" required minlength="10" maxlength="500"
                            placeholder="Please provide a reason for your leave request..."
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                            Submit Request
                        </button>
                        <a href="{{ route('leave-requests.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
