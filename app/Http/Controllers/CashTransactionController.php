<?php

namespace App\Http\Controllers;

use App\Enums\CashTransactionCategory;
use App\Enums\RoleStatus;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CashTransactionController extends Controller
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
        return view('cash_transactions.index', [
            'categories' => CashTransactionCategory::cases(),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'category' => $request->query('category'),
            'type' => $request->query('type'),
        ]);
    }

    public function data(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $category = $request->input('category');
        $type = $request->input('type');

        $query = CashTransaction::query()
            ->with('user')
            ->when($from, function ($w) use ($from) {
                $w->whereDate('date', '>=', $from);
            })
            ->when($to, function ($w) use ($to) {
                $w->whereDate('date', '<=', $to);
            })
            ->when($category, function ($w) use ($category) {
                $w->where('category', $category);
            })
            ->when($type, function ($w) use ($type) {
                $w->where('type', $type);
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->select(['id', 'user_id', 'type', 'category', 'date', 'amount', 'description', 'file_path']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', fn(CashTransaction $e) => $e->date->format('d/m/Y'))
            ->addColumn('type_badge', function (CashTransaction $e) {
                if ($e->type === 'in') {
                    return '<span class="badge bg-success">Pemasukan</span>';
                }
                return '<span class="badge bg-danger">Pengeluaran</span>';
            })
            ->addColumn('category_label', function (CashTransaction $e) {
                $cat = CashTransactionCategory::tryFrom($e->category);
                return $cat ? $cat->label() : ucfirst($e->category);
            })
            ->addColumn('user', fn(CashTransaction $e) => e($e->user?->name ?? '-'))
            ->editColumn('amount', function (CashTransaction $e) {
                $color = $e->type === 'in' ? 'text-success' : 'text-danger';
                $sign = $e->type === 'in' ? '+' : '-';
                return '<span class="' . $color . '">' . $sign . ' Rp ' . number_format((float) $e->amount, 0, ',', '.') . '</span>';
            })
            ->addColumn('description', fn(CashTransaction $e) => e($e->description ?? '-'))
            ->addColumn('has_file', fn(CashTransaction $e) => $e->file_path ? '<i class="bi bi-file-earmark-check text-success"></i>' : '-')
            ->addColumn('action', function (CashTransaction $e) {
                $deleteUrl = route('buku-kas.destroy', $e);
                $viewUrl = $e->file_path ? asset($e->file_path) : null;
                $btns = '<div class="d-flex justify-content-end gap-1">';
                if ($viewUrl) {
                    $btns .= '<a href="' . e($viewUrl) . '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark"></i></a>';
                }
                $btns .= '<form method="POST" action="' . e($deleteUrl) . '" style="display:inline" onsubmit="return confirm(\'Hapus catatan kas ini?\');">';
                $btns .= csrf_field() . method_field('DELETE');
                $btns .= '<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>';
                $btns .= '</form></div>';
                return $btns;
            })
            ->rawColumns(['has_file', 'action', 'type_badge', 'amount'])
            ->toJson();
    }

    public function create(): View
    {
        return view('cash_transactions.create', [
            'categories' => CashTransactionCategory::cases(),
            'type' => request('type', 'out'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out'],
            'category' => ['required', 'string', 'max:50'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'max:5000', 'mimes:jpg,jpeg,png,pdf'], // max 5MB
        ]);

        $expense = new CashTransaction();
        $expense->user_id = Auth::id();
        $expense->type = $validated['type'];
        $expense->category = $validated['category'];
        $expense->date = $validated['date'];
        $expense->amount = $validated['amount'];
        $expense->description = $validated['description'];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('cash_transactions', 'public');
            $expense->file_path = 'storage/' . $path;
        }

        $expense->save();

        $msg = $validated['type'] === 'in' ? 'Pemasukan Kas' : 'Pengeluaran Kas';
        return redirect()->route('buku-kas.index')->with('success', "{$msg} berhasil dicatat.");
    }

    public function destroy(CashTransaction $cashTransaction)
    {
        $cashTransaction->delete();
        return redirect()->route('buku-kas.index')->with('success', 'Catatan kas berhasil dihapus.');
    }
}

