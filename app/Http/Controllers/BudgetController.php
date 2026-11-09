<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        $today = now()->format('Y-m-d');

        $budgetQuery = Budget::where('user_id', $userId);
        if ($user->isCoupled() && $partner = $user->partner()) {
            $budgetQuery->orWhere(function ($q) use ($partner) {
                $q->where('user_id', $partner->id)->where('is_couple', true);
            });
        }

        $budgets = $budgetQuery->with(['category', 'user'])->orderBy('start_date', 'desc')->get();

        $activeBudgets = $budgets->filter(fn ($b) => $b->is_active);
        $upcomingBudgets = $budgets->filter(fn ($b) => $b->start_date->format('Y-m-d') > $today);
        $pastBudgets = $budgets->filter(fn ($b) => $b->end_date->format('Y-m-d') < $today);

        $categories = Category::where('user_id', $userId)->where('type', 'expense')->orderBy('name')->get();
        $incomeCategories = Category::where('user_id', $userId)->where('type', 'income')->orderBy('name')->get();

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
        $userId = Auth::id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'amount' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
            'is_couple' => 'nullable|boolean',
        ]);

        $isCouple = $request->boolean('is_couple');
        Budget::create(array_merge($validated, ['user_id' => $userId, 'is_couple' => $isCouple]));

        return back()->with('success', 'Anggaran berhasil dibuat!');
    }

    /**
     * Update the specified budget.
     */
    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $isOwner = $budget->user_id === Auth::id();
        $isPartnerCouple = ! $isOwner && $budget->is_couple && Auth::user()->isCoupled() && $budget->user_id === Auth::user()->partner()?->id;

        if (! $isOwner && ! $isPartnerCouple) {
            abort(403);
        }

        $userId = $budget->user_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'amount' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
            'is_couple' => 'nullable|boolean',
        ]);

        $validated['is_couple'] = $request->boolean('is_couple');
        $budget->update($validated);

        return back()->with('success', 'Anggaran berhasil diperbarui!');
    }

    /**
     * Top-up/increase the budget amount.
     */
    public function topUp(Request $request, Budget $budget): RedirectResponse
    {
        $canManage = $budget->user_id === Auth::id() || ($budget->is_couple && Auth::user()->isCoupled() && $budget->user_id === Auth::user()->partner()?->id);
        if (! $canManage) {
            abort(403);
        }

        $userId = Auth::id();

        $validated = $request->validate([
            'add_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
            'record_as_income' => 'nullable|boolean',
            'income_category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
        ]);

        $addAmount = (float) $validated['add_amount'];
        $oldAmount = (float) $budget->amount;
        $newAmount = $oldAmount + $addAmount;

        if ($request->boolean('record_as_income')) {
            $incomeCategory = ! empty($validated['income_category_id'])
                ? Category::where('user_id', $userId)->where('id', $validated['income_category_id'])->where('type', 'income')->first()
                : Category::where('user_id', $userId)->where('type', 'income')->first();

            if (! $incomeCategory) {
                $incomeCategory = Category::create([
                    'user_id' => $userId,
                    'name' => 'Pemasukan Lain',
                    'type' => 'income',
                    'icon' => 'wallet',
                    'color' => '#10b981',
                ]);
            }

            Transaction::create([
                'user_id' => $userId,
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
     * Deduct/decrease the budget amount.
     */
    public function deduct(Request $request, Budget $budget): RedirectResponse
    {
        $canManage = $budget->user_id === Auth::id() || ($budget->is_couple && Auth::user()->isCoupled() && $budget->user_id === Auth::user()->partner()?->id);
        if (! $canManage) {
            abort(403);
        }

        $validated = $request->validate([
            'deduct_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $deductAmount = (float) $validated['deduct_amount'];
        $oldAmount = (float) $budget->amount;

        if ($deductAmount > $oldAmount) {
            return back()->withErrors(['deduct_amount' => 'Nominal pengurangan tidak boleh melebihi total kuota anggaran saat ini (Rp '.number_format($oldAmount, 0, ',', '.').').']);
        }

        $newAmount = max(0, $oldAmount - $deductAmount);

        $dateStr = now()->format('d/m/Y');
        $formattedDeduct = number_format($deductAmount, 0, ',', '.');
        $noteSuffix = ! empty($validated['notes']) ? ' ('.$validated['notes'].')' : '';
        $logLine = "[{$dateStr}] -Rp {$formattedDeduct}{$noteSuffix}";

        $newNotes = $budget->notes ? ($budget->notes."\n".$logLine) : $logLine;

        $budget->update([
            'amount' => $newAmount,
            'notes' => $newNotes,
        ]);

        return back()->with('success', 'Berhasil mengurangi Rp '.$formattedDeduct.' dari anggaran '.$budget->name.'!');
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $budget->delete();

        return back()->with('success', 'Anggaran berhasil dihapus!');
    }
}
