<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Employees</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.leave-types.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Leave Types</a>
                <a href="{{ route('admin.users.create') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Add Employee
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hire Date</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->department ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->position ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs px-2 py-1 rounded-full font-medium
                                        @if($user->role === 'admin') bg-purple-100 text-purple-700
                                        @elseif($user->role === 'manager') bg-blue-100 text-blue-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->manager->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $user->hire_date ? $user->hire_date->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">{{ $users->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
