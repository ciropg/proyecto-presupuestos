<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use AuthorizesRequests;

    public function publicIndex(): View
    {
        $budgets = Budget::query()
            ->published()
            ->withCount('budgetItems')
            ->latest('budget_date')
            ->latest()
            ->paginate(9);

        return view('welcome', [
            'budgets' => $budgets,
        ]);
    }

    public function publicShow(Budget $budget): View
    {
        abort_unless($budget->isPubliclyVisible(), 404);

        $budget->load([
            'rootItems.resource.category',
            'rootItems.unit',
            'rootItems.childrenRecursive',
        ])->loadCount(['items as budget_items_count']);

        return view('budgets.public-show', [
            'budget' => $budget,
        ]);
    }

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
        $data = $this->syncPublicationState($data);
        $data['total_cost'] = 0;

        Budget::create($data);

        return redirect()
            ->route('budgets.index')
            ->with('success', __('Budget created successfully.'));
    }

    public function show(Budget $budget): View
    {
        $this->authorize('view', $budget);

        $budget->load([
            'user',
            'rootItems.resource.category',
            'rootItems.unit',
            'rootItems.childrenRecursive',
        ]);

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

        $data = $this->syncPublicationState($request->validated());

        $budget->update($data);

        return redirect()
            ->route('budgets.index')
            ->with('success', __('Budget updated successfully.'));
    }

    public function updatePublication(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorize('publish', $budget);

        $request->validate([
            'published' => ['required', 'boolean'],
        ]);

        $shouldPublish = $request->boolean('published');

        $budget->update($this->buildPublicationData($budget, $shouldPublish));

        return redirect()
            ->route('budgets.show', $budget)
            ->with(
                'success',
                $shouldPublish
                    ? __('Budget published successfully.')
                    : __('Budget unpublished successfully.')
            );
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        try {
            $budget->delete();
        } catch (QueryException) {
            return redirect()
                ->route('budgets.index')
                ->with('error', __('Budget could not be deleted because it is in use.'));
        }

        return redirect()
            ->route('budgets.index')
            ->with('success', __('Budget deleted successfully.'));
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function syncPublicationState(array $data): array
    {
        $data['is_published'] = ($data['status'] ?? null) === Budget::STATUS_PUBLISHED;

        return $data;
    }

    /**
     * @return array{status: string, is_published: bool}
     */
    private function buildPublicationData(Budget $budget, bool $shouldPublish): array
    {
        if ($shouldPublish) {
            return [
                'status' => Budget::STATUS_PUBLISHED,
                'is_published' => true,
            ];
        }

        return [
            'status' => $budget->status === Budget::STATUS_PUBLISHED
                ? Budget::STATUS_DRAFT
                : $budget->status,
            'is_published' => false,
        ];
    }
}
