<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        $budgets = Budget::with('category')->orderBy('start_date', 'desc')->get();

        $activeBudgets = $budgets->filter(fn ($b) => $b->is_active);
        $upcomingBudgets = $budgets->filter(fn ($b) => $b->start_date->format('Y-m-d') > $today);
        $pastBudgets = $budgets->filter(fn ($b) => $b->end_date->format('Y-m-d') < $today);

        $categories = Category::where('type', 'expense')->orderBy('name')->get();

        // Calculate overall active budgets summary
        $totalActiveBudgetAmount = $activeBudgets->sum('amount');
        $totalActiveBudgetSpent = $activeBudgets->sum(fn ($b) => $b->spent_amount);
        $totalActiveBudgetRemaining = max(0, $totalActiveBudgetAmount - $totalActiveBudgetSpent);

        return view('budgets.index', compact(
            'activeBudgets',
            'upcomingBudgets',
            'pastBudgets',
            'categories',
            'totalActiveBudgetAmount',
            'totalActiveBudgetSpent',
            'totalActiveBudgetRemaining'
        ));
    }

    public function store(Request $request)
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

    public function update(Request $request, Budget $budget)
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

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return back()->with('success', 'Anggaran berhasil dihapus!');
    }
}
