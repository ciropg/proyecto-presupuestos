<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Budget::class);

        $user = auth()->user();

        $budgets = Budget::query()
            ->with('user')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->latest('budget_date')
            ->latest()
            ->paginate(10);

        return view('budgets.index', [
            'budgets' => $budgets,
            'statuses' => Budget::statusOptions(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Budget::class);

        return view('budgets.create', $this->getFormData());
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->authorize('create', Budget::class);

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_published'] = $data['status'] === Budget::STATUS_PUBLISHED;
        $data['total_cost'] = 0;

        Budget::create($data);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function show(Budget $budget): View
    {
        $this->authorize('view', $budget);

        $budget->load('user');

        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);

        return view('budgets.edit', [
            'budget' => $budget,
            ...$this->getFormData(),
        ]);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $data = $request->validated();
        $data['is_published'] = $data['status'] === Budget::STATUS_PUBLISHED;

        $budget->update($data);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        try {
            $budget->delete();
        } catch (QueryException) {
            return redirect()
                ->route('budgets.index')
                ->with('error', 'Budget could not be deleted because it is in use.');
        }

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    /**
     * @return array{statuses: array<string, string>}
     */
    private function getFormData(): array
    {
        return [
            'statuses' => Budget::statusOptions(),
        ];
    }
}
