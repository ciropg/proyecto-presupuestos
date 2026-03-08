<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetItemRequest;
use App\Http\Requests\UpdateBudgetItemRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Resource;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BudgetItemController extends Controller
{
    use AuthorizesRequests;

    public function create(Budget $budget): View
    {
        $this->authorize('update', $budget);

        return view('budgets.item-create', [
            'budget' => $budget,
            ...$this->getFormData(),
        ]);
    }

    public function store(StoreBudgetItemRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $validated = $request->validated();
        $resource = $this->resolveResource($validated);

        BudgetItem::query()->create(
            $this->buildBudgetItemData($budget, $resource, $validated)
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
            ...$this->getFormData(),
        ]);
    }

    public function update(UpdateBudgetItemRequest $request, Budget $budget, BudgetItem $budgetItem): RedirectResponse
    {
        $this->authorize('update', $budget);

        $budgetItem = $this->resolveBudgetItem($budget, $budgetItem);
        $validated = $request->validated();
        $resource = $this->resolveResource($validated);

        $budgetItem->update(
            $this->buildBudgetItemData($budget, $resource, $validated)
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
     *     quantity: numeric-string|int|float,
     *     resource_id?: int|string|null,
     *     unit_id?: int|string|null,
     *     unit_price: numeric-string|int|float
     * }  $validated
     * @return array<string, mixed>
     */
    private function buildBudgetItemData(Budget $budget, ?Resource $resource, array $validated): array
    {
        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $name = $resource?->name ?? trim((string) ($validated['name'] ?? ''));
        $unitId = $resource?->unit_id ?? (int) ($validated['unit_id'] ?? 0);

        return [
            'budget_id' => $budget->id,
            'resource_id' => $resource?->id,
            'unit_id' => $unitId,
            'name' => $name,
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'subtotal' => round($quantity * $unitPrice, 2),
        ];
    }

    /**
     * @return array{
     *     resources: \Illuminate\Database\Eloquent\Collection<int, Resource>,
     *     units: \Illuminate\Database\Eloquent\Collection<int, Unit>
     * }
     */
    private function getFormData(): array
    {
        return [
            'resources' => Resource::query()
                ->with(['category', 'unit'])
                ->orderBy('name')
                ->get(),
            'units' => Unit::query()
                ->orderBy('name')
                ->get(),
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

    private function resolveBudgetItem(Budget $budget, BudgetItem $budgetItem): BudgetItem
    {
        abort_unless($budgetItem->budget_id === $budget->id, 404);

        return $budgetItem;
    }
}
