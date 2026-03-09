@php
    $depth = $depth ?? 0;
    $ancestorIds = $ancestorIds ?? [];
    $showActions = $showActions ?? false;
    $hasChildren = $budgetItem->children->isNotEmpty();
    $visibilityExpression = empty($ancestorIds)
        ? 'true'
        : implode(' && ', array_map(static fn (int $id): string => "(expanded[$id] ?? false)", $ancestorIds));
    $nextAncestorIds = [...$ancestorIds, $budgetItem->id];
@endphp

<tr
    @if ($ancestorIds !== [])
        x-cloak
        x-show="{{ $visibilityExpression }}"
    @endif
>
    <td class="px-6 py-4 text-sm text-gray-900">
        <div class="flex items-start gap-3" style="padding-left: {{ $depth * 1.5 }}rem;">
            @if ($hasChildren)
                <button
                    type="button"
                    x-on:click="toggle({{ $budgetItem->id }})"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    aria-label="{{ __('Toggle subitems') }}"
                >
                    <span x-show="!(expanded[{{ $budgetItem->id }}] ?? false)">+</span>
                    <span x-show="expanded[{{ $budgetItem->id }}] ?? false">-</span>
                </button>
            @else
                <span class="inline-flex h-7 w-7 items-center justify-center text-gray-300">-</span>
            @endif

            <div>
                <div class="font-medium">{{ $budgetItem->resource?->name ?? $budgetItem->name }}</div>
                <div class="mt-1 text-gray-500">{{ $budgetItem->description ?: '-' }}</div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">{{ $budgetItem->resource?->category?->name ?? __('Manual item') }}</td>
    <td class="px-6 py-4 text-sm text-gray-600">
        {{ $budgetItem->unit->name }} ({{ $budgetItem->unit->symbol }})
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->quantity, 4) }}</td>
    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->unit_price, 2) }}</td>
    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format((float) $budgetItem->subtotal, 2) }}</td>

    @if ($showActions)
        <td class="px-6 py-4">
            <div class="flex flex-wrap justify-end gap-2">
                <a
                    href="{{ route('budgets.items.create', ['budget' => $budget, 'parent' => $budgetItem->id]) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                >
                    {{ __('Add Subitem') }}
                </a>

                <a
                    href="{{ route('budgets.items.edit', [$budget, $budgetItem]) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                >
                    {{ __('Edit') }}
                </a>

                <form method="POST" action="{{ route('budgets.items.destroy', [$budget, $budgetItem]) }}">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500"
                        onclick="return confirm('{{ __('Delete this item?') }}')"
                    >
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </td>
    @endif
</tr>

@foreach ($budgetItem->children as $childItem)
    @include('budgets.partials.hierarchy-row', [
        'budget' => $budget,
        'budgetItem' => $childItem,
        'depth' => $depth + 1,
        'ancestorIds' => $nextAncestorIds,
        'showActions' => $showActions,
    ])
@endforeach
