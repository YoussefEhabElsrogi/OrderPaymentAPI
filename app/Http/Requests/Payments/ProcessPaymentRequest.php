<?php

namespace App\Http\Requests\Payments;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
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
        $rules = [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ];

        // Add payment method specific validation
        $paymentMethod = $this->input('payment_method');

        switch ($paymentMethod) {
            case PaymentMethod::PAYPAL->value:
                $rules['paypal_email'] = ['required', 'email'];
                break;

            case PaymentMethod::CREDIT_CARD->value:
                $rules['card_number'] = ['required', 'string'];
                $rules['expiry_month'] = ['required', 'integer', 'min:1', 'max:12'];
                $rules['expiry_year'] = ['required', 'integer', 'min:' . date('Y')];
                $rules['cvv'] = ['required', 'string', 'regex:/^\d{3,4}$/'];
                $rules['cardholder_name'] = ['required', 'string', 'max:255'];
                break;

            case PaymentMethod::BANK_TRANSFER->value:
                $rules['account_number'] = ['required', 'string', 'regex:/^\d{8,20}$/'];
                $rules['routing_number'] = ['required', 'string', 'regex:/^\d{9}$/'];
                $rules['account_holder_name'] = ['required', 'string', 'max:255'];
                $rules['bank_name'] = ['sometimes', 'string', 'max:255'];
                break;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method is required',
            'payment_method.enum' => 'Invalid payment method',
            'paypal_email.required' => 'PayPal email is required',
            'paypal_email.email' => 'PayPal email must be a valid email address',
            'card_number.required' => 'Card number is required',
            'expiry_month.required' => 'Expiry month is required',
            'expiry_month.integer' => 'Expiry month must be an integer',
            'expiry_month.min' => 'Expiry month must be between 1 and 12',
            'expiry_month.max' => 'Expiry month must be between 1 and 12',
            'expiry_year.required' => 'Expiry year is required',
            'expiry_year.integer' => 'Expiry year must be an integer',
            'expiry_year.min' => 'Expiry year cannot be in the past',
            'cvv.required' => 'CVV is required',
            'cvv.regex' => 'CVV must be 3 or 4 digits',
            'cardholder_name.required' => 'Cardholder name is required',
            'account_number.required' => 'Account number is required',
            'account_number.regex' => 'Account number must be 8-20 digits',
            'routing_number.required' => 'Routing number is required',
            'routing_number.regex' => 'Routing number must be 9 digits',
            'account_holder_name.required' => 'Account holder name is required',
        ];
    }
}
