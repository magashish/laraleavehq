<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Employee</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    @include('admin.users._form')
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create</button>
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
