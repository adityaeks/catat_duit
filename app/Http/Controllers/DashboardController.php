<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();
        $today = now();

        $userTransactions = Transaction::where('user_id', $userId);

        $totalIncome = (float) (clone $userTransactions)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $userTransactions)->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpense;

        $thisMonthIncome = (float) (clone $userTransactions)->where('type', 'income')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $thisMonthExpense = (float) (clone $userTransactions)->where('type', 'expense')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $todayExpense = (float) (clone $userTransactions)->where('type', 'expense')
            ->whereDate('transaction_date', $today->toDateString())
            ->sum('amount');

        $todayExpenseCount = (clone $userTransactions)->where('type', 'expense')
            ->whereDate('transaction_date', $today->toDateString())
            ->count();

        $allBudgets = Budget::where('user_id', $userId)
            ->with('category')
            ->latest('start_date')
            ->get();
        $activeBudgets = $allBudgets->filter(fn ($b) => $b->is_active);

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->latest('transaction_date')
            ->latest('id')
            ->take(7)
            ->get();

        $expenseCategories = Category::where('user_id', $userId)
            ->where('type', 'expense')
            ->withSum(['transactions' => function ($q) use ($today, $userId) {
                $q->where('user_id', $userId)
                    ->whereYear('transaction_date', $today->year)
                    ->whereMonth('transaction_date', $today->month);
            }], 'amount')
            ->get()
            ->filter(fn ($cat) => ($cat->transactions_sum_amount ?? 0) > 0)
            ->sortByDesc('transactions_sum_amount')
            ->values();

        $allCategories = Category::where('user_id', $userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact(
            'totalBalance',
            'thisMonthIncome',
            'thisMonthExpense',
            'todayExpense',
            'todayExpenseCount',
            'activeBudgets',
            'allBudgets',
            'recentTransactions',
            'expenseCategories',
            'allCategories'
        ));
    }
}
