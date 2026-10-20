<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\OcrSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
    }

    public function test_can_record_new_transaction(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create([
            'name' => 'Belanja Supermarket',
            'type' => 'expense',
            'icon' => 'shopping-cart',
            'color' => '#8b5cf6',
        ]);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $category->id,
            'amount' => 125000,
            'transaction_date' => '2026-03-10',
            'description' => 'Beli sabun dan minyak goreng',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 125000,
            'description' => 'Beli sabun dan minyak goreng',
        ]);
    }

    public function test_can_delete_transaction(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create([
            'name' => 'Kategori Hapus Test',
            'type' => 'expense',
            'icon' => 'trash-2',
            'color' => '#ef4444',
        ]);

        $tx = $user->transactions()->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-03-10',
            'description' => 'Untuk dihapus',
        ]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $tx));

        $response->assertRedirect();
        $this->assertDatabaseMissing('transactions', [
            'id' => $tx->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = $userA->categories()->create([
            'name' => 'Kategori A',
            'type' => 'expense',
            'icon' => 'tags',
            'color' => '#10b981',
        ]);

        $txA = $userA->transactions()->create([
            'category_id' => $catA->id,
            'type' => 'expense',
            'amount' => 75000,
            'transaction_date' => '2026-10-10',
            'description' => 'Milik User A',
        ]);

        // User B tries to delete User A's transaction
        $response = $this->actingAs($userB)->delete(route('transactions.destroy', $txA));
        $response->assertStatus(403);

        $this->assertDatabaseHas('transactions', [
            'id' => $txA->id,
        ]);
    }

    public function test_can_receive_and_parse_shared_receipt(): void
    {
        $user = User::factory()->create();
        $foodCat = $user->categories()->create([
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#f97316',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.share-receive', [
            'text' => 'Transfer Berhasil Sebesar Rp 45.000 ke Kopi Kenangan via BCA',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('quick_add=1', $targetUrl);
        $this->assertStringContainsString('shared_amount=45000', $targetUrl);
        $this->assertStringContainsString('shared_category_id='.$foodCat->id, $targetUrl);
    }

    public function test_store_cleans_dirty_shared_query_parameters_on_redirect(): void
    {
        $user = User::factory()->create();
        $cat = $user->categories()->create([
            'name' => 'Belanja Harian',
            'type' => 'expense',
            'icon' => 'shopping-cart',
            'color' => '#10b981',
        ]);

        $refererWithShareParams = 'http://localhost/dashboard?quick_add=1&shared_amount=50000&shared_type=expense';

        $response = $this->actingAs($user)
            ->from($refererWithShareParams)
            ->post(route('transactions.store'), [
                'type' => 'expense',
                'category_id' => $cat->id,
                'amount' => 50000,
                'transaction_date' => '2026-10-11',
                'description' => 'Belanja minimarket',
            ]);

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringNotContainsString('quick_add=1', $targetUrl);
        $this->assertStringNotContainsString('shared_amount=50000', $targetUrl);
        $this->assertEquals('http://localhost/dashboard', $targetUrl);
    }

    public function test_can_scan_receipt_via_ocr_space(): void
    {
        $user = User::factory()->create();
        $user->categories()->create([
            'name' => 'Belanja Harian',
            'type' => 'expense',
            'icon' => 'shopping-cart',
            'color' => '#10b981',
        ]);

        Http::fake([
            'api.ocr.space/*' => Http::response([
                'ParsedResults' => [
                    [
                        'ParsedText' => "INDOMARET POINT\nJL RAYA UTAMA NO 12\nTGL: 14/10/2026 15:30\nROTI TAWAR 15.000\nSUSU UHT 20.000\nTOTAL : 35.000\nTUNAI : 50.000\nKEMBALI : 15.000\nTERIMA KASIH",
                    ],
                ],
                'IsErroredOnProcessing' => false,
            ], 200),
        ]);

        $fakeFile = UploadedFile::fake()->image('receipt.jpg', 600, 800);

        $response = $this->actingAs($user)->postJson(route('transactions.scan-receipt'), [
            'receipt' => $fakeFile,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount' => 35000,
                'transaction_date' => '2026-10-14',
                'description' => 'Indomaret',
                'type' => 'expense',
            ]);
    }

    public function test_ocr_space_service_extracts_receipt_text_correctly(): void
    {
        $service = new OcrSpaceService;

        $rawIndomaret = "INDOMARET POINT\nTOTAL : Rp 48.500\nTUNAI : 50.000\n14/10/2026";
        $data = $service->extractReceiptData($rawIndomaret);

        $this->assertEquals(48500, $data['amount']);
        $this->assertEquals('2026-10-14', $data['transaction_date']);
        $this->assertEquals('Indomaret', $data['description']);
    }
}
