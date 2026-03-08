@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $resource->name ?? '')"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description', $resource->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="category_id" :value="__('Category')" />
        <select
            id="category_id"
            name="category_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">{{ __('Select a category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $resource->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
    </div>

    <div>
        <x-input-label for="unit_id" :value="__('Unit')" />
        <select
            id="unit_id"
            name="unit_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">{{ __('Select a unit') }}</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) old('unit_id', $resource->unit_id ?? '') === (string) $unit->id)>
                    {{ $unit->name }} ({{ $unit->symbol }})
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('unit_id')" />
    </div>

    <div>
        <x-input-label for="unit_price" :value="__('Unit Price')" />
        <x-text-input
            id="unit_price"
            name="unit_price"
            type="number"
            min="0"
            step="0.01"
            class="mt-1 block w-full"
            :value="old('unit_price', $resource->unit_price ?? '')"
            required
        />
        <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>

        <a
            href="{{ route('admin.resources.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</div>
