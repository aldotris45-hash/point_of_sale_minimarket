<?php

namespace App\Http\Requests\Expense;

use App\Enums\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categories = implode(',', array_column(ExpenseCategory::cases(), 'value'));

        return [
            'category' => ['required', 'in:' . $categories],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'max:5000', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category' => 'Kategori',
            'expense_date' => 'Tanggal Pengeluaran',
            'amount' => 'Jumlah',
            'description' => 'Keterangan',
            'file' => 'Bukti (Foto/PDF)',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'file' => ':attribute harus berupa file.',
            'mimes' => ':attribute harus berformat: jpg, jpeg, png, atau pdf.',
            'max' => ':attribute maksimal :max karakter / :max KB.',
        ];
    }
}
