@php
    $item = $budgetItem ?? null;
    $selectedResourceId = (string) old('resource_id', $item?->resource_id ?? '');
    $resourceData = $resources
        ->mapWithKeys(fn ($resource) => [
            (string) $resource->id => [
                'category' => $resource->category->name,
                'unit' => $resource->unit->name.' ('.$resource->unit->symbol.')',
                'unit_price' => number_format((float) $resource->unit_price, 2, '.', ''),
            ],
        ])
        ->all();
@endphp

@csrf

<div
    x-data="{
        resources: {{ \Illuminate\Support\Js::from($resourceData) }},
        selectedId: '{{ $selectedResourceId }}',
        categoryLabel: '',
        unitLabel: '',
        quantity: '{{ old('quantity', $item ? (float) $item->quantity : '') }}',
        unitPrice: '{{ old('unit_price', $item ? number_format((float) $item->unit_price, 2, '.', '') : '') }}',
        subtotal: '0.00',
        syncSelectedResource(resetPrice = false) {
            const resource = this.resources[this.selectedId] ?? null;

            this.categoryLabel = resource ? resource.category : '';
            this.unitLabel = resource ? resource.unit : '';

            if (resource && (resetPrice || this.unitPrice === '')) {
                this.unitPrice = resource.unit_price;
            }

            this.updateSubtotal();
        },
        updateSubtotal() {
            const quantity = Number.parseFloat(this.quantity || 0);
            const unitPrice = Number.parseFloat(this.unitPrice || 0);
            const subtotal = Number.isFinite(quantity) && Number.isFinite(unitPrice)
                ? quantity * unitPrice
                : 0;

            this.subtotal = subtotal.toFixed(2);
        },
        init() {
            this.syncSelectedResource(false);
        },
    }"
    class="space-y-6"
>
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
        <p><span class="font-medium">{{ __('Budget') }}:</span> {{ $budget->code }} - {{ $budget->title }}</p>
        <p class="mt-1">{{ __('Selecting a resource keeps the unit aligned with the catalog. You can adjust quantity, description and unit price if needed.') }}</p>
    </div>

    <div>
        <x-input-label for="resource_id" :value="__('Resource')" />
        <select
            id="resource_id"
            name="resource_id"
            x-model="selectedId"
            x-on:change="syncSelectedResource(true)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">{{ __('Select a resource') }}</option>
            @foreach ($resources as $resource)
                <option value="{{ $resource->id }}" @selected($selectedResourceId === (string) $resource->id)>
                    {{ $resource->name }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('resource_id')" />
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label :value="__('Category')" />
            <input
                type="text"
                x-model="categoryLabel"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                readonly
            />
        </div>

        <div>
            <x-input-label :value="__('Unit')" />
            <input
                type="text"
                x-model="unitLabel"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                readonly
            />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea
            id="description"
            name="description"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description', $item?->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div>
            <x-input-label for="quantity" :value="__('Quantity')" />
            <x-text-input
                id="quantity"
                name="quantity"
                type="number"
                min="0.0001"
                step="0.0001"
                class="mt-1 block w-full"
                x-model="quantity"
                x-on:input="updateSubtotal()"
                :value="old('quantity', $item?->quantity ?? '')"
                required
            />
            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
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
                x-model="unitPrice"
                x-on:input="updateSubtotal()"
                :value="old('unit_price', $item?->unit_price ?? '')"
                required
            />
            <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
        </div>

        <div>
            <x-input-label :value="__('Subtotal')" />
            <input
                type="text"
                x-model="subtotal"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                readonly
            />
        </div>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>

        <a
            href="{{ route('budgets.show', $budget) }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __('Cancel') }}
        </a>
    </div>
</div>
