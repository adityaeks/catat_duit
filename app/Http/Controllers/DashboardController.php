<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now();
        $totalIncome = (float) Transaction::where('type', 'income')->sum('amount');
        $totalExpense = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpense;

        $thisMonthIncome = (float) Transaction::where('type', 'income')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $thisMonthExpense = (float) Transaction::where('type', 'expense')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $allBudgets = Budget::with('category')->latest('start_date')->get();
        $activeBudgets = $allBudgets->filter(fn ($b) => $b->is_active);

        $recentTransactions = Transaction::with('category')
            ->latest('transaction_date')
            ->latest('id')
            ->take(7)
            ->get();

        $expenseCategories = Category::where('type', 'expense')
            ->withSum(['transactions' => function ($q) use ($today) {
                $q->whereYear('transaction_date', $today->year)
                    ->whereMonth('transaction_date', $today->month);
            }], 'amount')
            ->get()
            ->filter(fn ($cat) => ($cat->transactions_sum_amount ?? 0) > 0)
            ->sortByDesc('transactions_sum_amount')
            ->values();

        $allCategories = Category::orderBy('type')->orderBy('name')->get();

        return view('dashboard', compact(
            'totalBalance',
            'thisMonthIncome',
            'thisMonthExpense',
            'activeBudgets',
            'allBudgets',
            'recentTransactions',
            'expenseCategories',
            'allCategories'
        ));
    }
}
