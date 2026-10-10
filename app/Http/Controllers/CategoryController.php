<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $expenseCategories = Category::where('type', 'expense')
            ->withCount('transactions')
            ->withSum('transactions', 'amount')
            ->orderBy('name')
            ->get();

        $incomeCategories = Category::where('type', 'income')
            ->withCount('transactions')
            ->withSum('transactions', 'amount')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('expenseCategories', 'incomeCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        Category::create($validated);

        return back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        if ($category->transactions()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah memiliki transaksi terkait.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
