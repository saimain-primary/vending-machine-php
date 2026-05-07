<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255', Rule::unique('products', 'name')->ignore($this->route('product'))],
            'price' => ['sometimes', 'numeric', 'min:0.01'],
            'quantity_available' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Product name must be at least 2 characters.',
            'name.max' => 'Product name must not exceed 255 characters.',
            'name.unique' => 'A product with this name already exists.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least $0.01.',
            'quantity_available.integer' => 'Quantity must be a whole number.',
            'quantity_available.min' => 'Quantity cannot be negative.',
        ];
    }
}
