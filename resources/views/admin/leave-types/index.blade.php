<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leave Types</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Employees</a>
                <a href="{{ route('admin.leave-types.create') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Add Leave Type
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days/Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requires Approval</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requests</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaveTypes as $type)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <span class="w-4 h-4 rounded-full" style="background-color: {{ $type->color }}"></span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $type->name }}</p>
                                            @if($type->description)
                                                <p class="text-xs text-gray-500">{{ Str::limit($type->description, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $type->days_per_year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $type->requires_approval ? 'Yes' : 'No' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs px-2 py-1 rounded-full {{ $type->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $type->leave_requests_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    <a href="{{ route('admin.leave-types.edit', $type) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.leave-types.destroy', $type) }}" class="inline"
                                        onsubmit="return confirm('Delete this leave type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
