<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="employee" {{ old('role', $user->role ?? '') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="manager" {{ old('role', $user->role ?? '') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
            <select name="manager_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">No Manager</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" {{ old('manager_id', $user->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
            <input type="text" name="position" value="{{ old('position', $user->position ?? '') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
        <input type="date" name="hire_date" value="{{ old('hire_date', isset($user->hire_date) ? $user->hire_date->toDateString() : '') }}"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        @error('hire_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
