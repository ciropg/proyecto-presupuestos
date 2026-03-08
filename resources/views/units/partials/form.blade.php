@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $unit->name ?? '')"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="symbol" :value="__('Abbreviation')" />
        <x-text-input
            id="symbol"
            name="symbol"
            type="text"
            class="mt-1 block w-full"
            :value="old('symbol', $unit->symbol ?? '')"
            required
        />
        <x-input-error class="mt-2" :messages="$errors->get('symbol')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>

        <a
            href="{{ route('admin.units.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</div>
