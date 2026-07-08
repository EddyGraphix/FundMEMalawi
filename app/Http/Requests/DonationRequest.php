<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_email' => ['required', 'email', 'max:255'],
            'donor_phone' => ['nullable', 'string', 'max:20'],
            'is_anonymous' => ['boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:airtel_money,tnm_mpamba,card,bank_transfer'],

            'card_number' => ['exclude_unless:payment_method,card', 'required', 'string', 'size:16'],
            'expiry' => ['exclude_unless:payment_method,card', 'required', 'string', 'regex:/^\d{2}\/\d{2}$/'],
            'cvv' => ['exclude_unless:payment_method,card', 'required', 'string', 'size:3'],
            'cardholder_name' => ['exclude_unless:payment_method,card', 'required', 'string', 'max:255'],
        ];
    }
}
