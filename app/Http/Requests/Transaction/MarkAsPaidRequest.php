<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class MarkAsPaidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paid_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'paid_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'paid_amount.numeric'  => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min'      => 'Jumlah pembayaran tidak boleh negatif.',
        ];
    }
}
