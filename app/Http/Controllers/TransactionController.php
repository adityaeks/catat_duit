<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\OcrSpaceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $query = Transaction::where('user_id', $userId)
            ->with('category');

        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $today = now();
        $preset = $request->query('preset');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($preset && ! $startDate && ! $endDate) {
            switch ($preset) {
                case 'today':
                    $startDate = $today->toDateString();
                    $endDate = $today->toDateString();
                    break;
                case 'this_month':
                    $startDate = $today->copy()->startOfMonth()->toDateString();
                    $endDate = $today->copy()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $startDate = $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
                    $endDate = $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();
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
        }

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
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

        // Prepare Expense Chart Data
        $expenseChartDaily = [];
        $expenseChartCategories = [];
        $expenseChartMaxDaily = 0;
        $expenseChartPeakDay = null;
        $expenseChartDailyAvg = 0;

        if ($totalFilteredExpense > 0) {
            $expenseBaseQuery = (clone $query)->where('type', 'expense')->reorder();

            // 1. Category Breakdown
            $categorySums = (clone $expenseBaseQuery)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->with('category')
                ->get();

            $circumference = 2 * M_PI * 58;
            $currentOffset = 0;
            $catCount = $categorySums->count();

            foreach ($categorySums as $catSum) {
                $pct = $totalFilteredExpense > 0 ? ($catSum->total / $totalFilteredExpense) * 100 : 0;
                $strokeLength = ($pct / 100) * $circumference;
                $gap = ($catCount > 1 && $strokeLength > 3) ? 2.5 : 0;
                $dashLength = max(0.5, $strokeLength - $gap);
                $dashGap = $circumference - $dashLength;

                $expenseChartCategories[] = [
                    'id' => $catSum->category_id,
                    'name' => $catSum->category->name ?? 'Lainnya',
                    'color' => $catSum->category->color ?? '#f43f5e',
                    'icon' => $catSum->category->icon ?? 'tag',
                    'total' => (float) $catSum->total,
                    'percentage' => round($pct, 1),
                    'stroke_dasharray' => sprintf('%.2f %.2f', $dashLength, $dashGap),
                    'stroke_dashoffset' => sprintf('%.2f', -$currentOffset),
                ];

                $currentOffset += $strokeLength;
            }

            // 2. Daily Trend
            if ($request->filled('start_date') || $request->filled('end_date')) {
                $firstExpenseDate = (clone $expenseBaseQuery)->min('transaction_date');
                $lastExpenseDate = (clone $expenseBaseQuery)->max('transaction_date');

                $startStr = $request->start_date ?: ($firstExpenseDate ? Carbon::parse($firstExpenseDate)->toDateString() : now()->toDateString());
                $endStr = $request->end_date ?: ($lastExpenseDate ? Carbon::parse($lastExpenseDate)->toDateString() : now()->toDateString());

                $startDateObj = Carbon::parse($startStr)->startOfDay();
                $endDateObj = Carbon::parse($endStr)->startOfDay();

                if ($startDateObj->gt($endDateObj)) {
                    [$startDateObj, $endDateObj] = [$endDateObj, $startDateObj];
                }

                $diffDays = $startDateObj->diffInDays($endDateObj);

                $dailyRaw = (clone $expenseBaseQuery)
                    ->selectRaw('DATE(transaction_date) as t_date, SUM(amount) as total')
                    ->groupBy('t_date')
                    ->pluck('total', 't_date')
                    ->toArray();

                if ($diffDays <= 31) {
                    $curr = $startDateObj->copy();
                    while ($curr->lte($endDateObj)) {
                        $dateStr = $curr->format('Y-m-d');
                        $amount = (float) ($dailyRaw[$dateStr] ?? 0);
                        $expenseChartDaily[] = [
                            'date' => $dateStr,
                            'short_label' => $curr->translatedFormat('d M'),
                            'day_name' => $curr->translatedFormat('D'),
                            'amount' => $amount,
                        ];
                        $curr->addDay();
                    }
                } else {
                    foreach ($dailyRaw as $dateStr => $amount) {
                        $dObj = Carbon::parse($dateStr);
                        $expenseChartDaily[] = [
                            'date' => $dateStr,
                            'short_label' => $dObj->translatedFormat('d M'),
                            'day_name' => $dObj->translatedFormat('D'),
                            'amount' => (float) $amount,
                        ];
                    }
                    usort($expenseChartDaily, fn ($a, $b) => strcmp($a['date'], $b['date']));
                }
            } else {
                $latestActiveDates = (clone $expenseBaseQuery)
                    ->selectRaw('DATE(transaction_date) as t_date, SUM(amount) as total')
                    ->groupBy('t_date')
                    ->orderByDesc('t_date')
                    ->take(14)
                    ->get()
                    ->sortBy('t_date')
                    ->values();

                foreach ($latestActiveDates as $row) {
                    $dObj = Carbon::parse($row->t_date);
                    $expenseChartDaily[] = [
                        'date' => $row->t_date,
                        'short_label' => $dObj->translatedFormat('d M'),
                        'day_name' => $dObj->translatedFormat('D'),
                        'amount' => (float) $row->total,
                    ];
                }
            }

            if (! empty($expenseChartDaily)) {
                $expenseChartMaxDaily = max(array_column($expenseChartDaily, 'amount'));
                $daysWithSpend = array_filter($expenseChartDaily, fn ($d) => $d['amount'] > 0);
                $spendingDaysCount = count($daysWithSpend) ?: 1;
                $expenseChartDailyAvg = round($totalFilteredExpense / $spendingDaysCount);

                foreach ($expenseChartDaily as $d) {
                    if ($d['amount'] === $expenseChartMaxDaily && $expenseChartMaxDaily > 0) {
                        $expenseChartPeakDay = $d;
                        break;
                    }
                }
            }
        }

        $transactions = (clone $query)->latest('transaction_date')->latest('id')->paginate(20)->withQueryString();
        $categories = Category::where('user_id', $userId)->orderBy('type')->orderBy('name')->get();

        return view('transactions.index', compact(
            'transactions',
            'categories',
            'totalFilteredIncome',
            'totalFilteredExpense',
            'netFiltered',
            'expenseChartDaily',
            'expenseChartCategories',
            'expenseChartMaxDaily',
            'expenseChartPeakDay',
            'expenseChartDailyAvg',
            'startDate',
            'endDate',
            'preset'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        Transaction::create(array_merge($validated, ['user_id' => $userId]));

        $referer = $request->headers->get('referer');
        if ($referer) {
            $parsedUrl = parse_url($referer);
            if (! empty($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                $keysToRemove = ['quick_add', 'shared_amount', 'shared_type', 'shared_category_id', 'shared_description', 'shared_raw'];
                $hasDirtyParam = false;
                foreach ($keysToRemove as $key) {
                    if (isset($queryParams[$key])) {
                        unset($queryParams[$key]);
                        $hasDirtyParam = true;
                    }
                }

                if ($hasDirtyParam) {
                    $cleanUrl = ($parsedUrl['scheme'] ?? 'http').'://'.($parsedUrl['host'] ?? 'localhost').(isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '').($parsedUrl['path'] ?? '/');
                    if (! empty($queryParams)) {
                        $cleanUrl .= '?'.http_build_query($queryParams);
                    }

                    return redirect($cleanUrl)->with('success', 'Transaksi berhasil disimpan!');
                }
            }
        }

        return back()->with('success', 'Transaksi berhasil disimpan!');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $userId = Auth::id();

        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transaksi berhasil diperbarui!');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus!');
    }

    public function scanReceipt(Request $request, OcrSpaceService $ocrService): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        $result = $ocrService->scanReceipt($request->file('receipt'));

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal memproses struk dengan OCR.',
            ], 422);
        }

        // Remap category_id to user's category if possible
        $userId = Auth::id();
        $userCategoryId = null;
        if (! empty($result['category_id'])) {
            $cat = Category::find($result['category_id']);
            if ($cat) {
                $userCat = Category::where('user_id', $userId)->where('name', $cat->name)->first();
                $userCategoryId = $userCat?->id;
            }
        }

        return response()->json([
            'success' => true,
            'amount' => $result['amount'],
            'transaction_date' => $result['transaction_date'],
            'description' => $result['description'],
            'category_id' => $userCategoryId ?? $result['category_id'],
            'type' => $result['type'],
            'raw_text' => $result['raw_text'] ?? '',
        ]);
    }

    public function receiveShare(Request $request, OcrSpaceService $ocrService): RedirectResponse
    {
        // 1. If an image file was shared via Web Share Target (e.g. SeaBank screenshot/receipt)
        if ($request->hasFile('receipt')) {
            $ocrResult = $ocrService->scanReceipt($request->file('receipt'));
            if ($ocrResult['success']) {
                $userId = Auth::id();
                $userCategoryId = null;
                if (! empty($ocrResult['category_id'])) {
                    $cat = Category::find($ocrResult['category_id']);
                    if ($cat) {
                        $userCat = Category::where('user_id', $userId)->where('name', $cat->name)->first();
                        $userCategoryId = $userCat?->id;
                    }
                }

                return redirect()->route('dashboard', [
                    'quick_add' => 1,
                    'shared_amount' => $ocrResult['amount'],
                    'shared_type' => $ocrResult['type'],
                    'shared_category_id' => $userCategoryId ?? $ocrResult['category_id'],
                    'shared_description' => $ocrResult['description'],
                    'shared_raw' => substr($ocrResult['raw_text'] ?? '', 0, 150),
                ])->with('success', 'Struk gambar berhasil dipindai dan dimasukkan ke form transaksi!');
            }
        }

        // 2. Text-based share (e.g. text copied or shared directly from SeaBank / banking apps)
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
        $userId = Auth::id();
        $amount = 0;
        $type = 'expense';
        $categoryId = null;

        // 1. Extract Amount (Handles SeaBank, BCA, Mandiri, BRI, QRIS, e-Wallets)
        if (preg_match('/(?:Rp|IDR|Sebesar|Jumlah(?:\s*Transfer)?|Total(?:\s*Pembayaran)?|Nominal(?:\s*Transfer)?)\s*[:.]?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?|[0-9]+)/i', $text, $matches)) {
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

        $allCategories = Category::where('user_id', $userId)->get();

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

        // 3. Clean Description (Specific to SeaBank & Indonesian Banks)
        // If "Penerima: ..." exists (common in SeaBank text shares), extract the recipient directly
        $description = '';
        if (preg_match('/(?:Penerima|Nama\s*Penerima|Merchant|Ke)\s*[:]\s*([^\n\r,]+)/i', $text, $recMatch)) {
            $description = trim($recMatch[1]);
        }

        if (! $description) {
            $cleanedDesc = preg_replace('/(berhasil|sukses|transaksi|pembayaran|transfer|sebesar|nominal|total|ke|dari|via|seabank|sea\s*bank|bca|mandiri|bni|bri|gopay|ovo|dana|shopeepay|qris|rp\s*[0-9.,]+)/i', '', $text);
            $cleanedDesc = trim(preg_replace('/\s+/', ' ', $cleanedDesc));
            $description = $cleanedDesc ? substr($cleanedDesc, 0, 80) : 'Pembayaran Resi';
        }

        return [
            'amount' => $amount,
            'type' => $type,
            'category_id' => $categoryId,
            'description' => substr($description, 0, 80),
        ];
    }
}
