<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use Illuminate\Http\Request;

class KasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kas::with('user')->latest();

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $histories = $query->paginate(15)->withQueryString();
        $currentBalance = Kas::currentBalance();

        $totalIncome  = Kas::where('type', 'income')->sum('amount');
        $totalExpense = Kas::where('type', 'expense')->sum('amount');

        return view('kas.index', compact('histories', 'currentBalance', 'totalIncome', 'totalExpense'));
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $balanceBefore = Kas::currentBalance();
        $balanceAfter  = $balanceBefore + $request->amount;

        Kas::create([
            'type'           => 'income',
            'amount'         => $request->amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'description'    => $request->description,
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('kas.index')->with('success', 'Saldo kas berhasil ditambahkan! Saldo sekarang: Rp ' . number_format($balanceAfter, 0, ',', '.'));
    }

    public function expense(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $balanceBefore = Kas::currentBalance();

        if ($request->amount > $balanceBefore) {
            return redirect()->back()->withErrors(['amount' => 'Saldo kas tidak cukup! Saldo saat ini: Rp ' . number_format($balanceBefore, 0, ',', '.')])->withInput();
        }

        $balanceAfter = $balanceBefore - $request->amount;

        Kas::create([
            'type'           => 'expense',
            'amount'         => $request->amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'description'    => $request->description,
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('kas.index')->with('success', 'Pengeluaran berhasil dicatat! Sisa saldo: Rp ' . number_format($balanceAfter, 0, ',', '.'));
    }

    public function destroy(Kas $kas)
    {
        $kas->delete();
        return redirect()->route('kas.index')->with('success', 'Data berhasil dihapus.');
    }
}
