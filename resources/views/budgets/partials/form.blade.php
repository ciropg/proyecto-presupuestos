@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input
            id="code"
            name="code"
            type="text"
            class="mt-1 block w-full"
            :value="old('code', $budget->code ?? '')"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input
            id="title"
            name="title"
            type="text"
            class="mt-1 block w-full"
            :value="old('title', $budget->title ?? '')"
            required
        />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description', $budget->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="budget_date" :value="__('Date')" />
        <x-text-input
            id="budget_date"
            name="budget_date"
            type="date"
            class="mt-1 block w-full"
            :value="old('budget_date', isset($budget) && $budget->budget_date ? $budget->budget_date->format('Y-m-d') : '')"
            required
        />
        <x-input-error class="mt-2" :messages="$errors->get('budget_date')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select
            id="status"
            name="status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">{{ __('Select a status') }}</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $budget->status ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
        <p>{{ __('You can add items and cost breakdown after saving the budget.') }}</p>
        <p class="mt-1">{{ __('Budgets with status Published are shown on the public home page.') }}</p>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>

        <a
            href="{{ route('budgets.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</div>
