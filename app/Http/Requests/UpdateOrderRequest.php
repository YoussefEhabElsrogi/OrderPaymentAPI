<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->id() === $this->order->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'notes' => ['sometimes', 'string', 'max:1000'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_name' => ['sometimes', 'string', 'max:255'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'items.*.price' => ['sometimes', 'numeric', 'min:0.01'],
            'items.*.description' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.sometimes' => 'Order items are required',
            'items.array' => 'Order items must be an array',
            'items.min' => 'At least one order item is required',
            'items.*.product_name.required' => 'Product name is required for each item',
            'items.*.product_name.string' => 'Product name must be a string',
            'items.*.quantity.sometimes' => 'Quantity is required for each item',
            'items.*.quantity.integer' => 'Quantity must be an integer',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'items.*.price.sometimes' => 'Price is required for each item',
            'items.*.price.numeric' => 'Price must be a number',
            'items.*.price.min' => 'Price must be greater than 0',
        ];
    }
}
