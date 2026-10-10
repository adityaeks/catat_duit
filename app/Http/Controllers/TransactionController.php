<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('category')->latest('transaction_date')->latest('id');

        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $totalFilteredIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalFilteredExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netFiltered = $totalFilteredIncome - $totalFilteredExpense;

        $transactions = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('type')->orderBy('name')->get();

        return view('transactions.index', compact(
            'transactions',
            'categories',
            'totalFilteredIncome',
            'totalFilteredExpense',
            'netFiltered'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        Transaction::create($validated);

        return back()->with('success', 'Transaksi berhasil disimpan!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transaksi berhasil diperbarui!');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus!');
    }

    public function receiveShare(Request $request)
    {
        $title = (string) $request->input('title', '');
        $text = (string) $request->input('text', '');
        $url = (string) $request->input('url', '');

        $combined = trim($title.' '.$text.' '.$url);

        $parsed = $this->parseSharedReceipt($combined);

        return redirect()->route('dashboard', [
            'quick_add' => 1,
            'shared_amount' => $parsed['amount'],
            'shared_type' => $parsed['type'],
            'shared_category_id' => $parsed['category_id'],
            'shared_description' => $parsed['description'],
            'shared_raw' => substr($combined, 0, 150),
        ])->with('success', 'Data resi pembayaran berhasil diterima!');
    }

    private function parseSharedReceipt(string $text): array
    {
        $amount = 0;
        $type = 'expense';
        $categoryId = null;

        // 1. Extract Amount
        if (preg_match('/(?:Rp|IDR|Sebesar|Jumlah|Total|Nominal)\s*[:.]?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?|[0-9]+)/i', $text, $matches)) {
            $cleaned = preg_replace('/[^0-9]/', '', $matches[1]);
            if (is_numeric($cleaned) && (int) $cleaned > 0) {
                $amount = (int) $cleaned;
            }
        }

        // Fallback amount: any formatted number like 50.000
        if (! $amount && preg_match('/(?:^|\s)([0-9]{1,3}(?:\.[0-9]{3})+)(?:$|\s)/', $text, $matches)) {
            $amount = (int) preg_replace('/[^0-9]/', '', $matches[1]);
        }

        // 2. Guess Type & Category
        $lower = strtolower($text);

        if (preg_match('/(gaji|salary|payroll|bonus|thr|dividen|terima dana|dana masuk|pemasukan)/i', $lower)) {
            $type = 'income';
        }

        $allCategories = Category::all();

        $keywordMap = [
            'Makanan & Minuman' => ['makan', 'resto', 'kopi', 'coffee', 'cafe', 'warung', 'kuliner', 'bakso', 'ayam', 'mie', 'gofood', 'grabfood', 'shopeefood', 'f&b'],
            'Transportasi' => ['bensin', 'pertamina', 'shell', 'spbu', 'parkir', 'tol', 'gojek', 'grab', 'maxim', 'kereta', 'kai', 'krl', 'ojek'],
            'Belanja Harian' => ['indomaret', 'alfamart', 'superindo', 'tokopedia', 'shopee', 'lazada', 'blibli', 'tiktok shop', 'belanja', 'minimarket'],
            'Tagihan & Utilitas' => ['listrik', 'pln', 'pdam', 'indihome', 'biznet', 'wifi', 'pulsa', 'telkomsel', 'xl', 'indosat', 'bpjs', 'asuransi', 'pajak'],
            'Hiburan & Hobi' => ['bioskop', 'xxi', 'cgv', 'netflix', 'spotify', 'game', 'steam', 'playstation', 'karaoke'],
            'Kesehatan' => ['apotek', 'kimia farma', 'k24', 'dokter', 'klinik', 'rumah sakit', 'obat', 'lab'],
            'Gaji Utama' => ['gaji', 'salary', 'payroll'],
            'Bonus & THR' => ['bonus', 'thr', 'reward'],
        ];

        foreach ($keywordMap as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $found = $allCategories->firstWhere('name', $catName);
                    if ($found) {
                        $categoryId = $found->id;
                        $type = $found->type;
                        break 2;
                    }
                }
            }
        }

        if (! $categoryId) {
            $defaultCat = $allCategories->where('type', $type)->first();
            $categoryId = $defaultCat?->id;
        }

        // 3. Clean Description
        $cleanedDesc = preg_replace('/(berhasil|sukses|transaksi|pembayaran|transfer|sebesar|nominal|total|ke|dari|via|bca|mandiri|bni|bri|gopay|ovo|dana|shopeepay|qris|rp\s*[0-9.,]+)/i', '', $text);
        $cleanedDesc = trim(preg_replace('/\s+/', ' ', $cleanedDesc));
        $description = $cleanedDesc ? substr($cleanedDesc, 0, 80) : 'Pembayaran Resi';

        return [
            'amount' => $amount,
            'type' => $type,
            'category_id' => $categoryId,
            'description' => $description,
        ];
    }
}
