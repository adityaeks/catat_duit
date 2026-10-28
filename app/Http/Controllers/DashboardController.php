<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $today = now();

        // 1. Resolve date range filter & presets
        if ($request->has('reset')) {
            $request->session()->forget(['dashboard_preset', 'dashboard_start_date', 'dashboard_end_date']);
        }

        $preset = $request->query('preset');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Check session if query params not provided and not resetting
        if (! $request->has('reset') && ! $preset && ! $startDate && ! $endDate) {
            $preset = $request->session()->get('dashboard_preset');
            $startDate = $request->session()->get('dashboard_start_date');
            $endDate = $request->session()->get('dashboard_end_date');
        }

        // Calculate dates based on preset if preset is specified
        if ($preset) {
            switch ($preset) {
                case 'this_month':
                    $startDate = $today->copy()->startOfMonth()->toDateString();
                    $endDate = $today->copy()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $startDate = $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
                    $endDate = $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();
                    break;
                case 'paycheck':
                    if ($today->day >= 25) {
                        $startDate = $today->copy()->day(25)->toDateString();
                        $endDate = $today->copy()->addMonthNoOverflow()->day(24)->toDateString();
                    } else {
                        $startDate = $today->copy()->subMonthNoOverflow()->day(25)->toDateString();
                        $endDate = $today->copy()->day(24)->toDateString();
                    }
                    break;
                case '7_days':
                    $startDate = $today->copy()->subDays(6)->toDateString();
                    $endDate = $today->toDateString();
                    break;
                case '30_days':
                    $startDate = $today->copy()->subDays(29)->toDateString();
                    $endDate = $today->toDateString();
                    break;
                case 'all':
                    $startDate = null;
                    $endDate = null;
                    break;
            }
        } elseif ($startDate || $endDate) {
            $preset = 'custom';
            if ($startDate && ! $endDate) {
                $endDate = $today->toDateString();
            } elseif (! $startDate && $endDate) {
                $startDate = Carbon::parse($endDate)->startOfMonth()->toDateString();
            }
        } else {
            // Default: This Month
            $preset = 'this_month';
            $startDate = $today->copy()->startOfMonth()->toDateString();
            $endDate = $today->copy()->endOfMonth()->toDateString();
        }

        // Ensure start <= end
        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // Save active filter to session
        $request->session()->put('dashboard_preset', $preset);
        $request->session()->put('dashboard_start_date', $startDate);
        $request->session()->put('dashboard_end_date', $endDate);

        // 2. Human Readable Period Labels
        [$periodLabel, $periodDateRangeText] = $this->formatPeriodLabels($preset, $startDate, $endDate);

        // 3. Transactions Base Query for User
        $userTransactions = Transaction::where('user_id', $userId);

        // All-Time Balance (for reference/total net worth)
        $allTimeIncome = (float) (clone $userTransactions)->where('type', 'income')->sum('amount');
        $allTimeExpense = (float) (clone $userTransactions)->where('type', 'expense')->sum('amount');
        $allTimeBalance = $allTimeIncome - $allTimeExpense;

        // Period Filtered Totals for the Card
        $periodQuery = clone $userTransactions;
        if ($startDate && $endDate) {
            $periodQuery->whereBetween('transaction_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $periodQuery->where('transaction_date', '>=', $startDate);
        } elseif ($endDate) {
            $periodQuery->where('transaction_date', '<=', $endDate);
        }

        $periodIncome = (float) (clone $periodQuery)->where('type', 'income')->sum('amount');
        $periodExpense = (float) (clone $periodQuery)->where('type', 'expense')->sum('amount');
        $periodBalance = $periodIncome - $periodExpense;
        $periodTransactionCount = (clone $periodQuery)->count();

        // Compatibility variables for view
        $totalBalance = $periodBalance;
        $thisMonthIncome = $periodIncome;
        $thisMonthExpense = $periodExpense;

        // Today's Expense
        $todayExpense = (float) (clone $userTransactions)->where('type', 'expense')
            ->whereDate('transaction_date', $today->toDateString())
            ->sum('amount');

        $todayExpenseCount = (clone $userTransactions)->where('type', 'expense')
            ->whereDate('transaction_date', $today->toDateString())
            ->count();

        // Budgets
        $allBudgets = Budget::where('user_id', $userId)
            ->with('category')
            ->latest('start_date')
            ->get();
        $activeBudgets = $allBudgets->filter(fn ($b) => $b->is_active);

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->latest('transaction_date')
            ->latest('id')
            ->take(7)
            ->get();

        // Expense Categories Breakdown (Filtered by selected period)
        $expenseCategories = Category::where('user_id', $userId)
            ->where('type', 'expense')
            ->withSum(['transactions' => function ($q) use ($startDate, $endDate, $userId) {
                $q->where('user_id', $userId);
                if ($startDate && $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $q->where('transaction_date', '>=', $startDate);
                } elseif ($endDate) {
                    $q->where('transaction_date', '<=', $endDate);
                }
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
            'allTimeBalance',
            'periodBalance',
            'periodIncome',
            'periodExpense',
            'periodTransactionCount',
            'periodLabel',
            'periodDateRangeText',
            'preset',
            'startDate',
            'endDate',
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

    /**
     * @return array{0: string, 1: string}
     */
    protected function formatPeriodLabels(?string $preset, ?string $startDate, ?string $endDate): array
    {
        if ($preset === 'all' || (! $startDate && ! $endDate)) {
            return ['Semua Waktu', 'Semua Riwayat Transaksi'];
        }

        $start = $startDate ? Carbon::parse($startDate) : null;
        $end = $endDate ? Carbon::parse($endDate) : null;

        $rangeText = ($start && $end)
            ? $start->translatedFormat('d M Y').' – '.$end->translatedFormat('d M Y')
            : ($start ? 'Mulai '.$start->translatedFormat('d M Y') : 'Hingga '.$end?->translatedFormat('d M Y'));

        return match ($preset) {
            'this_month' => ['Bulan Ini', $rangeText],
            'last_month' => ['Bulan Lalu', $rangeText],
            'paycheck' => ['Siklus Gajian', $rangeText],
            '7_days' => ['7 Hari Terakhir', $rangeText],
            '30_days' => ['30 Hari Terakhir', $rangeText],
            default => [
                ($start && $end) ? $start->translatedFormat('d M').' - '.$end->translatedFormat('d M') : 'Kustom',
                $rangeText,
            ],
        };
    }
}
