<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\OcrSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OcrSpaceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_parse_user_cafe_bee_receipt(): void
    {
        Category::firstOrCreate(
            ['name' => 'Makanan & Minuman', 'type' => 'expense'],
            ['icon' => 'utensils', 'color' => '#f97316']
        );

        $text = "BEEPOS\nCafe Bee\nJl. Kemang Selatan I No.20\nSurabaya, 021-33335800\nTanggal: 24-05-2023 16:45\nKasir : Hafiz\nNo Trx : JL/0022305241645/004\nMember : CASH\nChannel: Dine In\nBakso\n2 x 12,000\n24,000\nCah hangkung\n3 x 7,500\n22,500\nCap Jay\n3 x 17,000\n51,000\nCumi Goreng Tepung Gurih\n3 x 25,000\n75,000\nSubtotal\n172,500\nPajak\n18,975\nTotal\n191,475\nTUNA I\n200,000\nKembalian\n8,525\n** Supported by bee. id **";

        $service = new OcrSpaceService;
        $data = $service->extractReceiptData($text);

        $this->assertEquals(191475, $data['amount']);
        $this->assertEquals('2023-05-24', $data['transaction_date']);
        $this->assertEquals('Cafe Bee', $data['description']);
    }
}
