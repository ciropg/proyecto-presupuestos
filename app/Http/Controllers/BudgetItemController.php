<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetItemRequest;
use App\Http\Requests\UpdateBudgetItemRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Resource;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BudgetItemController extends Controller
{
    use AuthorizesRequests;

    public function create(Budget $budget, Request $request): View
    {
        $this->authorize('update', $budget);
        $parentItem = $this->resolveParentItem($budget, $request->integer('parent'));

        return view('budgets.item-create', [
            'budget' => $budget,
            ...$this->getFormData($budget, null, $parentItem),
        ]);
    }

    public function store(StoreBudgetItemRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $validated = $request->validated();
        $parentItem = $this->resolveParentItem($budget, (int) ($validated['parent_id'] ?? 0));
        $resource = $this->resolveResource($validated);

        BudgetItem::query()->create(
            $this->buildBudgetItemData($budget, $resource, $validated, $parentItem)
        );

        $budget->recalculateTotalCost();

        return redirect()
            ->route('budgets.show', $budget)
            ->with('success', 'Budget item added successfully.');
    }

    public function edit(Budget $budget, BudgetItem $budgetItem): View
    {
        $this->authorize('update', $budget);

        $budgetItem = $this->resolveBudgetItem($budget, $budgetItem);

        return view('budgets.item-edit', [
            'budget' => $budget,
            'budgetItem' => $budgetItem,
            ...$this->getFormData($budget, $budgetItem, $budgetItem->parent),
        ]);
    }

    public function update(UpdateBudgetItemRequest $request, Budget $budget, BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorize('update', $budget);

        $budgetItem = $this->resolveBudgetItem($budget, $budgetItem);
        $validated = $request->validated();
        $parentItem = $this->resolveParentItem($budget, (int) ($validated['parent_id'] ?? 0));
        $resource = $this->resolveResource($validated);

        $budgetItem->update(
            $this->buildBudgetItemData($budget, $resource, $validated, $parentItem, $budgetItem)
        );

        $budget->recalculateTotalCost();

        return redirect()
            ->route('budgets.show', $budget)
            ->with('success', 'Budget item updated successfully.');
    }

    public function destroy(Budget $budget, BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorize('update', $budget);

        $budgetItem = $this->resolveBudgetItem($budget, $budgetItem);

        $budgetItem->delete();
        $budget->recalculateTotalCost();

        return redirect()
            ->route('budgets.show', $budget)
            ->with('success', 'Budget item deleted successfully.');
    }

    /**
     * @param  array{
     *     description: string|null,
     *     name?: string|null,
     *     parent_id?: int|string|null,
     *     quantity: numeric-string|int|float,
     *     resource_id?: int|string|null,
     *     sort_order?: int|string|null,
     *     unit_id?: int|string|null,
     *     unit_price: numeric-string|int|float
     * }  $validated
     * @return array<string, mixed>
     */
    private function buildBudgetItemData(
        Budget $budget,
        ?Resource $resource,
        array $validated,
        ?BudgetItem $parentItem = null,
        ?BudgetItem $existingItem = null
    ): array {
        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $name = $resource?->name ?? trim((string) ($validated['name'] ?? ''));
        $unitId = $resource?->unit_id ?? (int) ($validated['unit_id'] ?? 0);
        $parentId = $parentItem?->id;
        $sortOrder = $this->resolveSortOrder($budget, $parentId, $validated, $existingItem);

        return [
            'budget_id' => $budget->id,
            'parent_id' => $parentId,
            'resource_id' => $resource?->id,
            'unit_id' => $unitId,
            'name' => $name,
            'description' => $validated['description'] ?? null,
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'subtotal' => round($quantity * $unitPrice, 2),
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @return array{
     *     resources: \Illuminate\Database\Eloquent\Collection<int, Resource>,
     *     units: \Illuminate\Database\Eloquent\Collection<int, Unit>,
     *     parentItem: BudgetItem|null,
     *     parentOptions: array<int, array{id: int, label: string}>,
     *     selectedParentId: string
     * }
     */
    private function getFormData(Budget $budget, ?BudgetItem $currentItem = null, ?BudgetItem $selectedParent = null): array
    {
        $budgetItems = $budget->items()
            ->ordered()
            ->get(['id', 'parent_id', 'name']);

        $excludedIds = $currentItem === null
            ? []
            : [
                $currentItem->id,
                ...$this->collectDescendantIds($budgetItems, $currentItem->id),
            ];

        return [
            'resources' => Resource::query()
                ->with(['category', 'unit'])
                ->orderBy('name')
                ->get(),
            'units' => Unit::query()
                ->orderBy('name')
                ->get(),
            'parentItem' => $selectedParent,
            'selectedParentId' => (string) old('parent_id', $selectedParent?->id ?? $currentItem?->parent_id ?? ''),
            'parentOptions' => $this->flattenParentOptions($budgetItems, null, 0, $excludedIds),
        ];
    }

    /**
     * @param  array{resource_id?: int|string|null}  $validated
     */
    private function resolveResource(array $validated): ?Resource
    {
        $resourceId = $validated['resource_id'] ?? null;

        if ($resourceId === null || $resourceId === '') {
            return null;
        }

        return Resource::query()->findOrFail((int) $resourceId);
    }

    private function resolveParentItem(Budget $budget, int $parentId): ?BudgetItem
    {
        if ($parentId === 0) {
            return null;
        }

        return $budget->items()
            ->select(['id', 'budget_id', 'parent_id', 'name', 'description'])
            ->findOrFail($parentId);
    }

    /**
     * @param  array{sort_order?: int|string|null}  $validated
     */
    private function resolveSortOrder(
        Budget $budget,
        ?int $parentId,
        array $validated,
        ?BudgetItem $existingItem = null
    ): int {
        if (isset($validated['sort_order']) && $validated['sort_order'] !== null && $validated['sort_order'] !== '') {
            return (int) $validated['sort_order'];
        }

        if ($existingItem !== null && $existingItem->parent_id === $parentId && $existingItem->sort_order !== null) {
            return (int) $existingItem->sort_order;
        }

        $siblings = $budget->items()
            ->when(
                $parentId === null,
                fn ($query) => $query->whereNull('parent_id'),
                fn ($query) => $query->where('parent_id', $parentId)
            );

        if ($existingItem !== null) {
            $siblings->where('id', '!=', $existingItem->id);
        }

        $maxSortOrder = $siblings->max('sort_order');

        return $maxSortOrder === null ? 1 : ((int) $maxSortOrder + 1);
    }

    /**
     * @param  Collection<int, BudgetItem>  $items
     * @param  array<int, int>  $excludedIds
     * @return array<int, array{id: int, label: string}>
     */
    private function flattenParentOptions(
        Collection $items,
        ?int $parentId = null,
        int $depth = 0,
        array $excludedIds = []
    ): array {
        $options = [];

        foreach ($items->where('parent_id', $parentId) as $item) {
            if (! in_array($item->id, $excludedIds, true)) {
                $options[] = [
                    'id' => $item->id,
                    'label' => str_repeat('-- ', $depth).$item->name,
                ];
            }

            $options = [
                ...$options,
                ...$this->flattenParentOptions($items, $item->id, $depth + 1, $excludedIds),
            ];
        }

        return $options;
    }

    /**
     * @param  Collection<int, BudgetItem>  $items
     * @return array<int, int>
     */
    private function collectDescendantIds(Collection $items, int $parentId): array
    {
        $descendantIds = [];

        foreach ($items->where('parent_id', $parentId) as $item) {
            $descendantIds[] = $item->id;
            $descendantIds = [
                ...$descendantIds,
                ...$this->collectDescendantIds($items, $item->id),
            ];
        }

        return $descendantIds;
    }

    private function resolveBudgetItem(Budget $budget, BudgetItem $budgetItem): BudgetItem
    {
        abort_unless($budgetItem->budget_id === $budget->id, 404);

        return $budgetItem;
    }
}
