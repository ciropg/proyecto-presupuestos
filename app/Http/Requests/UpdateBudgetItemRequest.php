<?php

namespace App\Http\Requests;

use App\Models\Budget;
use App\Models\BudgetItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateBudgetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Budget $budget */
        $budget = $this->route('budget');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('budget_items', 'id')->where(
                    fn ($query) => $query->where('budget_id', $budget->id)
                ),
            ],
            'resource_id' => ['nullable', 'integer', Rule::exists('resources', 'id')],
            'name' => ['nullable', 'string', 'max:150', 'required_without:resource_id'],
            'unit_id' => ['nullable', 'integer', Rule::exists('units', 'id'), 'required_without:resource_id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var BudgetItem $budgetItem */
                $budgetItem = $this->route('budgetItem');
                $parentId = $this->integer('parent_id');

                if ($parentId === 0) {
                    return;
                }

                if ($parentId === $budgetItem->id) {
                    $validator->errors()->add('parent_id', __('An item cannot be its own parent.'));

                    return;
                }

                if ($this->isDescendant($budgetItem, $parentId)) {
                    $validator->errors()->add('parent_id', __('An item cannot be assigned to one of its descendants.'));
                }
            },
        ];
    }

    private function isDescendant(BudgetItem $budgetItem, int $parentId): bool
    {
        $currentParent = BudgetItem::query()
            ->select(['id', 'parent_id'])
            ->find($parentId);

        while ($currentParent !== null) {
            if ($currentParent->id === $budgetItem->id) {
                return true;
            }

            if ($currentParent->parent_id === null) {
                return false;
            }

            $currentParent = BudgetItem::query()
                ->select(['id', 'parent_id'])
                ->find($currentParent->parent_id);
        }

        return false;
    }
}
