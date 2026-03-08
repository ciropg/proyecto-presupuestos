<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetItemRequest;
use App\Http\Requests\UpdateBudgetItemRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Resource;
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

        $resource = Resource::query()->findOrFail($request->integer('resource_id'));

        BudgetItem::query()->create(
            $this->buildBudgetItemData($budget, $resource, $request->validated())
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
        $resource = Resource::query()->findOrFail($request->integer('resource_id'));

        $budgetItem->update(
            $this->buildBudgetItemData($budget, $resource, $request->validated())
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
     * @param  array{description: string|null, quantity: numeric-string|int|float, unit_price: numeric-string|int|float}  $validated
     * @return array<string, mixed>
     */
    private function buildBudgetItemData(Budget $budget, Resource $resource, array $validated): array
    {
        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];

        return [
            'budget_id' => $budget->id,
            'resource_id' => $resource->id,
            'unit_id' => $resource->unit_id,
            'name' => $resource->name,
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'subtotal' => round($quantity * $unitPrice, 2),
        ];
    }

    /**
     * @return array{resources: \Illuminate\Database\Eloquent\Collection<int, Resource>}
     */
    private function getFormData(): array
    {
        return [
            'resources' => Resource::query()
                ->with(['category', 'unit'])
                ->orderBy('name')
                ->get(),
        ];
    }

    private function resolveBudgetItem(Budget $budget, BudgetItem $budgetItem): BudgetItem
    {
        abort_unless($budgetItem->budget_id === $budget->id, 404);

        return $budgetItem;
    }
}
