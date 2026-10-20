<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets.
     */
    public function index(): View
    {
        $today = now()->format('Y-m-d');
        $budgets = Budget::with('category')->orderBy('start_date', 'desc')->get();

        $activeBudgets = $budgets->filter(fn ($b) => $b->is_active);
        $upcomingBudgets = $budgets->filter(fn ($b) => $b->start_date->format('Y-m-d') > $today);
        $pastBudgets = $budgets->filter(fn ($b) => $b->end_date->format('Y-m-d') < $today);

        $categories = Category::where('type', 'expense')->orderBy('name')->get();
        $incomeCategories = Category::where('type', 'income')->orderBy('name')->get();

        // Calculate overall active budgets summary
        $totalActiveBudgetAmount = $activeBudgets->sum('amount');
        $totalActiveBudgetSpent = $activeBudgets->sum(fn ($b) => $b->spent_amount);
        $totalActiveBudgetRemaining = max(0, $totalActiveBudgetAmount - $totalActiveBudgetSpent);

        return view('budgets.index', compact(
            'activeBudgets',
            'upcomingBudgets',
            'pastBudgets',
            'categories',
            'incomeCategories',
            'totalActiveBudgetAmount',
            'totalActiveBudgetSpent',
            'totalActiveBudgetRemaining'
        ));
    }

    /**
     * Store a newly created budget.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
        ]);

        Budget::create($validated);

        return back()->with('success', 'Anggaran berhasil dibuat!');
    }

    /**
     * Update the specified budget.
     */
    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
        ]);

        $budget->update($validated);

        return back()->with('success', 'Anggaran berhasil diperbarui!');
    }

    /**
     * Top-up/increase the budget amount.
     */
    public function topUp(Request $request, Budget $budget): RedirectResponse
    {
        $validated = $request->validate([
            'add_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
            'record_as_income' => 'nullable|boolean',
            'income_category_id' => 'nullable|exists:categories,id',
        ]);

        $addAmount = (float) $validated['add_amount'];
        $oldAmount = (float) $budget->amount;
        $newAmount = $oldAmount + $addAmount;

        if ($request->boolean('record_as_income')) {
            $incomeCategory = ! empty($validated['income_category_id'])
                ? Category::where('id', $validated['income_category_id'])->where('type', 'income')->first()
                : Category::where('type', 'income')->first();

            if (! $incomeCategory) {
                $incomeCategory = Category::create([
                    'name' => 'Pemasukan Lain',
                    'type' => 'income',
                    'icon' => 'wallet',
                    'color' => '#10b981',
                ]);
            }

            Transaction::create([
                'category_id' => $incomeCategory->id,
                'type' => 'income',
                'amount' => $addAmount,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Penambahan Anggaran: '.$budget->name.(! empty($validated['notes']) ? ' ('.$validated['notes'].')' : ''),
            ]);
        }

        $dateStr = now()->format('d/m/Y');
        $formattedAdd = number_format($addAmount, 0, ',', '.');
        $noteSuffix = ! empty($validated['notes']) ? ' ('.$validated['notes'].')' : '';
        $logLine = "[{$dateStr}] +Rp {$formattedAdd}{$noteSuffix}";

        $newNotes = $budget->notes ? ($budget->notes."\n".$logLine) : $logLine;

        $budget->update([
            'amount' => $newAmount,
            'notes' => $newNotes,
        ]);

        return back()->with('success', 'Berhasil menambahkan Rp '.$formattedAdd.' ke anggaran '.$budget->name.'!');
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return back()->with('success', 'Anggaran berhasil dihapus!');
    }
}
