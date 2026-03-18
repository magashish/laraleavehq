<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Employee &mdash; {{ $user->name }}</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Profile -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-base font-medium text-gray-900 mb-4">Profile</h3>
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf @method('PUT')
                    @include('admin.users._form', ['user' => $user])

                    <!-- Leave Balances -->
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Leave Balances ({{ now()->year }})</h4>
                        <div class="space-y-3">
                            @foreach($leaveTypes as $type)
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2 w-40">
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $type->color }}"></span>
                                        <span class="text-sm text-gray-700">{{ $type->name }}</span>
                                    </div>
                                    <input type="number" name="balances[{{ $type->id }}]"
                                        value="{{ $balances[$type->id]->allocated_days ?? $type->days_per_year }}"
                                        min="0" max="365" step="0.5"
                                        class="w-24 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                    <span class="text-xs text-gray-500">days allocated</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Save Changes</button>
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
