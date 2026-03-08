<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResourceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
