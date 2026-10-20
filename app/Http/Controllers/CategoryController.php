<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $expenseCategories = Category::where('user_id', $userId)
            ->where('type', 'expense')
            ->withCount(['transactions' => fn ($q) => $q->where('user_id', $userId)])
            ->withSum(['transactions' => fn ($q) => $q->where('user_id', $userId)], 'amount')
            ->orderBy('name')
            ->get();

        $incomeCategories = Category::where('user_id', $userId)
            ->where('type', 'income')
            ->withCount(['transactions' => fn ($q) => $q->where('user_id', $userId)])
            ->withSum(['transactions' => fn ($q) => $q->where('user_id', $userId)], 'amount')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('expenseCategories', 'incomeCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        Category::create(array_merge($validated, ['user_id' => $userId]));

        return back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        if ($category->transactions()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah memiliki transaksi terkait.');
        }

        if ($category->budgets()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah memiliki anggaran terkait.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
