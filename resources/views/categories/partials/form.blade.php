@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $category->name ?? '')"
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
        >{{ old('description', $category->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>

        <a
            href="{{ route('admin.categories.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</div>
