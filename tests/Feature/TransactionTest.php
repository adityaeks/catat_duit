<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $category = Category::firstOrCreate(
            ['name' => 'Belanja Supermarket', 'type' => 'expense'],
            ['icon' => 'shopping-cart', 'color' => '#8b5cf6']
        );

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $category->id,
            'amount' => 125000,
            'transaction_date' => '2026-03-10',
            'description' => 'Beli sabun dan minyak goreng',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'amount' => 125000,
            'description' => 'Beli sabun dan minyak goreng',
        ]);
    }

    public function test_can_delete_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::firstOrCreate(
            ['name' => 'Kategori Hapus Test', 'type' => 'expense'],
            ['icon' => 'trash-2', 'color' => '#ef4444']
        );

        $tx = Transaction::create([
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

    public function test_can_receive_and_parse_shared_receipt(): void
    {
        $user = User::factory()->create();
        $foodCat = Category::firstOrCreate(
            ['name' => 'Makanan & Minuman', 'type' => 'expense'],
            ['icon' => 'utensils', 'color' => '#f97316']
        );

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
        $cat = Category::firstOrCreate(
            ['name' => 'Belanja Harian', 'type' => 'expense'],
            ['icon' => 'shopping-cart', 'color' => '#10b981']
        );

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
}
