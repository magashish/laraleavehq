<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Leave Type</h2>
            <a href="{{ route('admin.leave-types.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.leave-types.store') }}">
                    @csrf
                    @include('admin.leave-types._form')
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create</button>
                        <a href="{{ route('admin.leave-types.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
