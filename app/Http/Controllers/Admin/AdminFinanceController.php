<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFinanceController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user:id,name')->latest()->get();
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return Inertia::render('Admin/Finance/Index', [
            'transactions' => $transactions,
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'balance' => $balance
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        Transaction::create($validated + ['user_id' => auth()->id()]);

        \App\Helpers\Logger::log('finance_entry', ($validated['type'] === 'income' ? 'Income' : 'Expense') . ' recorded: ' . $validated['amount']);

        return back()->with('success', 'Financial record secured.');
    }
}
