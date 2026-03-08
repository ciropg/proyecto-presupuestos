<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'resource_id' => ['nullable', 'integer', Rule::exists('resources', 'id')],
            'name' => ['nullable', 'string', 'max:150', 'required_without:resource_id'],
            'unit_id' => ['nullable', 'integer', Rule::exists('units', 'id'), 'required_without:resource_id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
