<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\WebPUtility;

class TransparencyController extends Controller
{
    public function index()
    {
        // Calculate totals
        $income = Transaction::where('type', 'income')->sum('amount');
        $expense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        // Get recent transactions
        $transactions = Transaction::with('user:id,name')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'amount' => $t->amount,
                    'type' => $t->type,
                    'category' => $t->category,
                    'description' => $t->description,
                    'date' => $t->transaction_date->format('Y-m-d'),
                    'receipt' => $t->receipt_path ? asset($t->receipt_path) : null,
                    'user' => $t->user->name,
                ];
            });

        return response()->json([
            'financials' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance
            ],
            'transactions' => $transactions
        ]);
    }

    /**
     * Store a new transaction (Treasurer only)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Treasurer' && $user->role !== 'SuperAdmin') {
            return response()->json(['message' => 'Unauthorized. Only Treasurers can add transactions.'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|image|max:2048'
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = WebPUtility::compress($request->file('receipt'), 'receipts');
        }

        $transaction = Transaction::create([
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
            'receipt_path' => $receiptPath,
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'transaction' => $transaction
        ], 201);
    }
}
