@php
    $item = $budgetItem ?? null;
    $selectedParentId = (string) ($selectedParentId ?? old('parent_id', $item?->parent_id ?? ''));
    $selectedResourceId = (string) old('resource_id', $item?->resource_id ?? '');
    $selectedUnitId = (string) old('unit_id', $item?->unit_id ?? '');
    $resourceData = $resources
        ->mapWithKeys(fn ($resource) => [
            (string) $resource->id => [
                'name' => $resource->name,
                'category' => $resource->category->name,
                'unit_id' => (string) $resource->unit_id,
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
        categoryLabel: '{{ __('Manual item') }}',
        name: '{{ old('name', $item?->name ?? '') }}',
        unitId: '{{ $selectedUnitId }}',
        quantity: '{{ old('quantity', $item ? (float) $item->quantity : '') }}',
        unitPrice: '{{ old('unit_price', $item ? number_format((float) $item->unit_price, 2, '.', '') : '') }}',
        subtotal: '0.00',
        syncSelectedResource(resetPrice = false) {
            const resource = this.resources[this.selectedId] ?? null;

            this.categoryLabel = resource ? resource.category : '{{ __('Manual item') }}';

            if (resource) {
                this.name = resource.name;
                this.unitId = resource.unit_id;
            }

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
        @if ($parentItem)
            <p class="mt-1"><span class="font-medium">{{ __('Parent item') }}:</span> {{ $parentItem->name }}</p>
        @endif
        <p class="mt-1">{{ __('You can create a root item or assign it under another item as a subitem. If the item later has children, its visible subtotal is calculated from those children.') }}</p>
    </div>

    <div>
        <x-input-label for="parent_id" :value="__('Parent Item')" />
        <select
            id="parent_id"
            name="parent_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="">{{ __('Root item / No parent') }}</option>
            @foreach ($parentOptions as $parentOption)
                <option value="{{ $parentOption['id'] }}" @selected($selectedParentId === (string) $parentOption['id'])>
                    {{ $parentOption['label'] }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">{{ __('Leave this empty to keep the item at the top level of the budget.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
    </div>

    <div>
        <x-input-label for="resource_id" :value="__('Resource')" />
        <select
            id="resource_id"
            name="resource_id"
            x-model="selectedId"
            x-on:change="syncSelectedResource(true)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="">{{ __('Manual item / No catalog resource') }}</option>
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
            <x-input-label for="name" :value="__('Item Name')" />
            <input
                id="name"
                name="name"
                type="text"
                x-model="name"
                x-bind:readonly="selectedId !== ''"
                x-bind:required="selectedId === ''"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p class="mt-1 text-xs text-gray-500" x-show="selectedId !== ''">{{ __('The item name is taken from the selected resource.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label :value="__('Category')" />
            <input
                type="text"
                x-model="categoryLabel"
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                readonly
            />
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="unit_id" :value="__('Unit')" />
            <select
                id="unit_id"
                name="unit_id"
                x-model="unitId"
                x-bind:disabled="selectedId !== ''"
                x-bind:required="selectedId === ''"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
            >
                <option value="">{{ __('Select a unit') }}</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected($selectedUnitId === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500" x-show="selectedId !== ''">{{ __('The unit is synchronized from the selected resource.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('unit_id')" />
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
