<?php

namespace App\Http\Requests;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetItemRequest extends FormRequest
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
}
