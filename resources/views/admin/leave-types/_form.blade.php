<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $leaveType->name ?? '') }}" required
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
        <input type="color" name="color" value="{{ old('color', $leaveType->color ?? '#3B82F6') }}"
            class="h-10 w-20 border-gray-300 rounded-md cursor-pointer" />
        @error('color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="3"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $leaveType->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Days Per Year</label>
        <input type="number" name="days_per_year" value="{{ old('days_per_year', $leaveType->days_per_year ?? 0) }}"
            min="0" max="365" required
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        @error('days_per_year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="requires_approval" value="1"
                {{ old('requires_approval', $leaveType->requires_approval ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600" />
            <span class="text-sm font-medium text-gray-700">Requires Approval</span>
        </label>

        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $leaveType->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600" />
            <span class="text-sm font-medium text-gray-700">Active</span>
        </label>
    </div>
</div>
