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
}
