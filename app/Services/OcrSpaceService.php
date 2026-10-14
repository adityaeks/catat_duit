<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrSpaceService
{
    /**
     * OCR.space endpoint URL.
     */
    protected string $endpoint;

    /**
     * OCR.space API key.
     */
    protected string $apiKey;

    /**
     * Create a new OcrSpaceService instance.
     */
    public function __construct()
    {
        $this->apiKey = (string) config('services.ocr_space.api_key', env('OCR_SPACE_API_KEY', 'K87899142388957'));
        $this->endpoint = (string) config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image');
    }

    /**
     * Scan and parse an uploaded receipt image using OCR.space.
     *
     * @return array{
     *     success: bool,
     *     message?: string,
     *     amount?: int,
     *     transaction_date?: string,
     *     description?: string,
     *     category_id?: int|null,
     *     type?: string,
     *     raw_text?: string
     * }
     */
    public function scanReceipt(UploadedFile $file): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'OCR.space API Key belum dikonfigurasi di file .env (OCR_SPACE_API_KEY).',
            ];
        }

        try {
            $response = Http::timeout(45)
                ->asMultipart()
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($this->endpoint, [
                    'apikey' => $this->apiKey,
                    'language' => 'eng',
                    'isOverlayRequired' => 'false',
                    'detectOrientation' => 'true',
                    'scale' => 'true',
                    'OCREngine' => '2',
                ]);

            if (! $response->successful()) {
                Log::error('OCR.space HTTP error', ['status' => $response->status(), 'body' => $response->body()]);

                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi server OCR.space (Status: '.$response->status().').',
                ];
            }

            $json = $response->json();

            if (! empty($json['IsErroredOnProcessing'])) {
                $errorMsg = is_array($json['ErrorMessage'] ?? null)
                    ? implode(', ', $json['ErrorMessage'])
                    : ($json['ErrorMessage'] ?? 'Terjadi kesalahan saat memproses gambar di OCR.space.');

                return [
                    'success' => false,
                    'message' => $errorMsg,
                ];
            }

            $rawText = $json['ParsedResults'][0]['ParsedText'] ?? '';

            if (empty(trim($rawText))) {
                return [
                    'success' => false,
                    'message' => 'Teks pada struk tidak terbaca atau gambar terlalu buram. Mohon coba foto lebih dekat dan jelas.',
                ];
            }

            $parsedData = $this->extractReceiptData($rawText);

            return array_merge(['success' => true, 'raw_text' => trim($rawText)], $parsedData);
        } catch (Exception $e) {
            Log::error('OCR.space exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kendala saat memproses gambar: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Extract amount, date, description, and category from OCR parsed text.
     *
     * @return array{
     *     amount: int,
     *     transaction_date: string,
     *     description: string,
     *     category_id: int|null,
     *     type: string
     * }
     */
    public function extractReceiptData(string $text): array
    {
        $amount = $this->extractTotalAmount($text);
        $date = $this->extractDate($text);
        $description = $this->extractMerchantDescription($text);
        $categoryInfo = $this->matchCategory($text, $description);

        return [
            'amount' => $amount,
            'transaction_date' => $date,
            'description' => $description,
            'category_id' => $categoryInfo['category_id'],
            'type' => $categoryInfo['type'],
        ];
    }

    /**
     * Extract total amount from receipt lines.
     */
    protected function extractTotalAmount(string $text): int
    {
        $rawLines = preg_split('/\r\n|\r|\n/', $text);
        $lines = [];
        foreach ($rawLines as $l) {
            $trimmed = trim($l);
            if ($trimmed !== '') {
                $lines[] = $trimmed;
            }
        }

        $count = count($lines);
        $candidates = [];

        // 1. Look for explicit keywords: TOTAL, GRAND TOTAL, TAGIHAN, JUMLAH, BAYAR, SUB TOTAL
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            // Exclude lines with Kembalian / Change
            if (preg_match('/(kembali|kembalian|change)/i', $line)) {
                continue;
            }

            // Check: Grand Total / Total Bayar / Total Belanja / Total / Tagihan
            $isTotal = preg_match('/^(?:grand\s*total|total\s*bayar|total\s*belanja|total\s*harga|total|tagihan|jumlah\s*bayar)\b/i', $line)
                || preg_match('/\b(?:grand\s*total|total\s*bayar|total\s*belanja|total\s*harga|total|tagihan)\b/i', $line);

            if ($isTotal) {
                // Check on same line
                if (preg_match('/(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $line, $match)) {
                    $num = $this->cleanNumber($match[1]);
                    if ($num >= 500) {
                        $candidates[] = ['priority' => 10, 'amount' => $num, 'index' => $i];

                        continue;
                    }
                }

                // If number is on NEXT line (e.g. "Total" on line i, "191,475" on line i+1)
                if ($i + 1 < $count) {
                    $nextLine = $lines[$i + 1];
                    if (preg_match('/^(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $nextLine, $match)) {
                        $num = $this->cleanNumber($match[1]);
                        if ($num >= 500) {
                            $candidates[] = ['priority' => 10, 'amount' => $num, 'index' => $i];

                            continue;
                        }
                    }
                }
            }

            // Check: Subtotal
            if (preg_match('/\b(?:sub\s*total|subtotal)\b/i', $line)) {
                if (preg_match('/(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $line, $match)) {
                    $num = $this->cleanNumber($match[1]);
                    if ($num >= 500) {
                        $candidates[] = ['priority' => 8, 'amount' => $num, 'index' => $i];

                        continue;
                    }
                }

                if ($i + 1 < $count) {
                    $nextLine = $lines[$i + 1];
                    if (preg_match('/^(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $nextLine, $match)) {
                        $num = $this->cleanNumber($match[1]);
                        if ($num >= 500) {
                            $candidates[] = ['priority' => 8, 'amount' => $num, 'index' => $i];

                            continue;
                        }
                    }
                }
            }

            // Check: Tunai / Cash / Debit / QRIS (lower priority than Total)
            if (preg_match('/\b(?:tuna\s*i|tunai|cash|debit|qris|kartu|bca|mandiri|bri)\b/i', $line)) {
                if (preg_match('/(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $line, $match)) {
                    $num = $this->cleanNumber($match[1]);
                    if ($num >= 500) {
                        $candidates[] = ['priority' => 5, 'amount' => $num, 'index' => $i];

                        continue;
                    }
                }

                if ($i + 1 < $count) {
                    $nextLine = $lines[$i + 1];
                    if (preg_match('/^(?:rp\.?|idr)?\s*([0-9]{1,3}(?:[.,][0-9]{3})+|[0-9]{3,})/i', $nextLine, $match)) {
                        $num = $this->cleanNumber($match[1]);
                        if ($num >= 500) {
                            $candidates[] = ['priority' => 5, 'amount' => $num, 'index' => $i];

                            continue;
                        }
                    }
                }
            }
        }

        if (! empty($candidates)) {
            // Sort by priority desc (Total=10 > Subtotal=8 > Tunai=5), then later in receipt (index desc)
            usort($candidates, function ($a, $b) {
                if ($a['priority'] === $b['priority']) {
                    return $b['index'] <=> $a['index'];
                }

                return $b['priority'] <=> $a['priority'];
            });

            return $candidates[0]['amount'];
        }

        // 2. Fallback: Search any formatted Indonesian currency pattern
        $allNumbers = [];
        if (preg_match_all('/(?:Rp\.?|IDR)?\s*([0-9]{1,3}(?:\.[0-9]{3})+|[0-9]{1,3}(?:,[0-9]{3})+)/i', $text, $matches)) {
            foreach ($matches[1] as $rawNum) {
                $num = $this->cleanNumber($rawNum);
                if ($num >= 1000 && $num !== 2023 && $num !== 2024 && $num !== 2025 && $num !== 2026 && $num !== 2027) {
                    $allNumbers[] = $num;
                }
            }
        }

        if (! empty($allNumbers)) {
            return max($allNumbers);
        }

        return 0;
    }

    /**
     * Clean and format numeric string to integer.
     */
    protected function cleanNumber(string $raw): int
    {
        // Standardize: remove non-digits
        $digits = preg_replace('/[^0-9]/', '', $raw);

        return (int) $digits;
    }

    /**
     * Extract transaction date from receipt.
     */
    protected function extractDate(string $text): string
    {
        // Pattern 1: DD/MM/YYYY or DD-MM-YYYY or DD.MM.YYYY
        if (preg_match('/(\b[0-3]?[0-9])[\/\.-]([0-1]?[0-9])[\/\.-](20[2-3][0-9]\b|\b[2-3][0-9]\b)/', $text, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];

            if ($year < 100) {
                $year += 2000;
            }

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Pattern 2: YYYY/MM/DD or YYYY-MM-DD
        if (preg_match('/(20[2-3][0-9])[\/\.-]([0-1]?[0-9])[\/\.-]([0-3]?[0-9])/', $text, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Default to today
        return Carbon::now()->format('Y-m-d');
    }

    /**
     * Extract store / merchant name from receipt header lines.
     */
    protected function extractMerchantDescription(string $text): string
    {
        $lower = strtolower($text);

        // Predefined chain detection
        $knownChains = [
            'indomaret' => 'Indomaret',
            'alfamart' => 'Alfamart',
            'alfamidi' => 'Alfamidi',
            'superindo' => 'Superindo',
            'hypermart' => 'Hypermart',
            'transmart' => 'Transmart',
            'spbu pertamina' => 'SPBU Pertamina',
            'pertamina' => 'Pertamina',
            'shell' => 'SPBU Shell',
            'starbucks' => 'Starbucks Coffee',
            'kopi kenangan' => 'Kopi Kenangan',
            'janji jiwa' => 'Kopi Janji Jiwa',
            'point coffee' => 'Point Coffee',
            'mcdonald' => "McDonald's",
            'mcd' => "McDonald's",
            'kfc' => 'KFC',
            'hokben' => 'HokBen',
            'pizza hut' => 'Pizza Hut',
            'dcrepes' => 'Dcrepes',
            'jco' => 'J.CO Donuts & Coffee',
            'solaria' => 'Solaria',
            'chatime' => 'Chatime',
            'mixue' => 'Mixue',
            'guardian' => 'Guardian',
            'watsons' => 'Watsons',
            'apotek k-24' => 'Apotek K-24',
            'kimia farma' => 'Kimia Farma',
            'gramedia' => 'Gramedia',
            'ace hardware' => 'ACE Hardware',
            'informa' => 'Informa',
            'miniso' => 'Miniso',
            'uniqlo' => 'Uniqlo',
        ];

        foreach ($knownChains as $pattern => $name) {
            if (str_contains($lower, $pattern)) {
                return $name;
            }
        }

        // Generic POS software brands to skip
        $posBrands = ['beepos', 'moka', 'pawoon', 'olsera', 'majoo', 'esb', 'qasir', 'kasir pintar', 'gobiz', 'hellobill', 'bee.id', 'pos'];

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $headerCandidates = [];

        foreach ($lines as $line) {
            $clean = trim(preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $line));
            if (strlen($clean) < 3) {
                continue;
            }

            $cleanLower = strtolower($clean);

            // Skip technical metadata lines
            if (preg_match('/^(jl|jalan|struk|nota|tgl|tanggal|kasir|no|pos|telp|pax|member|channel|meja|table)/i', $clean)) {
                continue;
            }

            // Skip POS software branding lines
            if (in_array($cleanLower, $posBrands, true)) {
                continue;
            }

            // High priority: Lines containing store keywords (Cafe, Kopi, Resto, Bakso, etc.)
            if (preg_match('/\b(cafe|kopi|coffee|resto|restoran|warung|bakso|kitchen|bakery|dapur|bar|depot|angkringan|kedai|bistro|steak|mart|market)\b/i', $clean)) {
                return substr($clean, 0, 40);
            }

            $headerCandidates[] = substr($clean, 0, 40);

            // Only check top 6 lines of receipt for merchant name
            if (count($headerCandidates) >= 4) {
                break;
            }
        }

        if (! empty($headerCandidates)) {
            return $headerCandidates[0];
        }

        return 'Pembelian Struk';
    }

    /**
     * Match receipt content to existing category.
     *
     * @return array{category_id: int|null, type: string}
     */
    protected function matchCategory(string $text, string $description): array
    {
        $allCategories = Category::all();
        $combined = strtolower($text.' '.$description);
        $type = 'expense';
        $categoryId = null;

        $keywordMap = [
            'Belanja Harian' => ['indomaret', 'alfamart', 'alfamidi', 'superindo', 'hypermart', 'transmart', 'tokopedia', 'shopee', 'lazada', 'blibli', 'belanja', 'minimarket', 'swalayan', 'market', 'sembako'],
            'Makanan & Minuman' => ['makan', 'resto', 'kopi', 'coffee', 'cafe', 'warung', 'kuliner', 'bakso', 'ayam', 'mie', 'gofood', 'grabfood', 'shopeefood', 'f&b', 'starbucks', 'kfc', 'mcd', 'hokben', 'pizza', 'solaria', 'chatime', 'mixue', 'dcrepes', 'tea'],
            'Transportasi' => ['bensin', 'pertamina', 'spbu', 'shell', 'parkir', 'tol', 'gojek', 'grab', 'maxim', 'kereta', 'kai', 'krl', 'ojek', 'bbm'],
            'Tagihan & Utilitas' => ['listrik', 'pln', 'pdam', 'indihome', 'biznet', 'wifi', 'pulsa', 'telkomsel', 'xl', 'indosat', 'bpjs', 'pajak'],
            'Kesehatan' => ['apotek', 'kimia farma', 'k24', 'k-24', 'dokter', 'klinik', 'rumah sakit', 'obat', 'guardian', 'watsons', 'vitamin'],
            'Hiburan & Hobi' => ['bioskop', 'xxi', 'cgv', 'game', 'steam', 'playstation', 'karaoke', 'miniso'],
        ];

        foreach ($keywordMap as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($combined, $kw)) {
                    $found = $allCategories->firstWhere('name', $catName);
                    if ($found) {
                        return [
                            'category_id' => $found->id,
                            'type' => $found->type,
                        ];
                    }
                }
            }
        }

        // Default category fallback
        $defaultCat = $allCategories->where('type', 'expense')->first();

        return [
            'category_id' => $defaultCat?->id,
            'type' => 'expense',
        ];
    }
}
