<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Enums\RoleStatus;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('expenses.index', [
            'categories' => ExpenseCategory::cases(),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'category' => $request->query('category'),
        ]);
    }

    public function data(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $category = $request->input('category');

        $query = Expense::query()
            ->with('user')
            ->when($from, function ($w) use ($from) {
                $w->whereDate('expense_date', '>=', $from);
            })
            ->when($to, function ($w) use ($to) {
                $w->whereDate('expense_date', '<=', $to);
            })
            ->when($category && in_array($category, array_column(ExpenseCategory::cases(), 'value'), true), function ($w) use ($category) {
                $w->where('category', $category);
            })
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->select(['id', 'user_id', 'category', 'expense_date', 'amount', 'description', 'file_path']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', fn(Expense $e) => $e->expense_date->format('d/m/Y'))
            ->addColumn('category_label', fn(Expense $e) => $e->category->label())
            ->addColumn('user', fn(Expense $e) => e($e->user?->name ?? '-'))
            ->editColumn('amount', fn(Expense $e) => 'Rp ' . number_format((float) $e->amount, 0, ',', '.'))
            ->addColumn('description', fn(Expense $e) => e($e->description ?? '-'))
            ->addColumn('has_file', fn(Expense $e) => $e->file_path ? '<i class="bi bi-file-earmark-check text-success"></i>' : '-')
            ->addColumn('action', function (Expense $e) {
                $editUrl = route('pengeluaran.edit', $e);
                $deleteUrl = route('pengeluaran.destroy', $e);
                $viewUrl = $e->file_path ? asset($e->file_path) : null;
                $btns = '<div class="d-flex justify-content-end gap-1">';
                $btns .= '<a href="' . e($editUrl) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>';
                if ($viewUrl) {
                    $btns .= '<a href="' . e($viewUrl) . '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark"></i></a>';
                }
                $btns .= '<form method="POST" action="' . e($deleteUrl) . '" style="display:inline" onsubmit="return confirm(\'Hapus pengeluaran ini?\');">';
                $btns .= csrf_field() . method_field('DELETE');
                $btns .= '<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>';
                $btns .= '</form></div>';
                return $btns;
            })
            ->rawColumns(['has_file', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('expenses.create', [
            'categories' => ExpenseCategory::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', array_column(ExpenseCategory::cases(), 'value'))],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'max:5000', 'mimes:jpg,jpeg,png,pdf'], // max 5MB
        ]);

        $expense = new Expense();
        $expense->user_id = Auth::id();
        $expense->category = $validated['category'];
        $expense->expense_date = $validated['expense_date'];
        $expense->amount = $validated['amount'];
        $expense->description = $validated['description'];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('expenses', 'public');
            $expense->file_path = 'storage/' . $path;
        }

        $expense->save();

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::cases(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', array_column(ExpenseCategory::cases(), 'value'))],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'max:5000', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $expense->category = $validated['category'];
        $expense->expense_date = $validated['expense_date'];
        $expense->amount = $validated['amount'];
        $expense->description = $validated['description'];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('expenses', 'public');
            $expense->file_path = 'storage/' . $path;
        }

        $expense->save();

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
