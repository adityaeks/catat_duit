<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertSee('Atur Anggaran');
    }

    public function test_can_create_budget_with_custom_date_range(): void
    {
        $user = User::factory()->create();
        $category = Category::firstOrCreate(
            ['name' => 'Kuliner Test', 'type' => 'expense'],
            ['icon' => 'utensils', 'color' => '#f97316']
        );

        $response = $this->actingAs($user)->post(route('budgets.store'), [
            'name' => 'Budget Gajian Khusus',
            'category_id' => $category->id,
            'amount' => 1500000,
            'start_date' => '2026-02-25',
            'end_date' => '2026-03-24',
            'notes' => 'Catatan alokasi gajian',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'name' => 'Budget Gajian Khusus',
            'amount' => 1500000,
        ]);

        $created = Budget::where('name', 'Budget Gajian Khusus')->first();
        $this->assertNotNull($created);
        $this->assertEquals('2026-02-25', $created->start_date->format('Y-m-d'));
        $this->assertEquals('2026-03-24', $created->end_date->format('Y-m-d'));
    }

    public function test_budget_calculates_spent_amount_only_within_date_range(): void
    {
        $category = Category::firstOrCreate(
            ['name' => 'Bensin Test', 'type' => 'expense'],
            ['icon' => 'car', 'color' => '#3b82f6']
        );

        $budget = Budget::create([
            'name' => 'Budget Bensin Periode Khusus',
            'category_id' => $category->id,
            'amount' => 500000,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-15',
        ]);

        // Transaction INSIDE date range
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150000,
            'transaction_date' => '2026-05-05',
            'description' => 'Isi bensin dalam periode',
        ]);

        // Transaction OUTSIDE date range (before)
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 80000,
            'transaction_date' => '2026-04-28',
            'description' => 'Isi bensin sebelum periode',
        ]);

        // Transaction OUTSIDE date range (after)
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => '2026-05-20',
            'description' => 'Isi bensin setelah periode',
        ]);

        $this->assertEquals(150000, $budget->spent_amount);
        $this->assertEquals(350000, $budget->remaining_amount);
        $this->assertEquals(30.0, $budget->percentage);
        $this->assertEquals('safe', $budget->status);
    }

    public function test_daily_recommendation_is_locked_during_current_day_and_recalculates_next_day(): void
    {
        Carbon::setTestNow('2026-10-11 10:00:00');

        $category = Category::firstOrCreate(
            ['name' => 'Makan', 'type' => 'expense'],
            ['icon' => 'utensils', 'color' => '#f97316']
        );

        $budget = Budget::create([
            'name' => 'Budget Harian Tetap',
            'amount' => 450000,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-25',
        ]);

        // Pengeluaran kemarin (10 Oct): Rp 50.000
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-10-10',
            'description' => 'Kemarin',
        ]);

        // Awal hari ini (11 Oct):
        // Sisa hari: 15 hari. Sisa sebelum hari ini: 400.000.
        // Batas aman: 400.000 / 15 = 26.667
        $this->assertEquals(15, $budget->days_remaining);
        $this->assertEquals(26667, $budget->daily_remaining_recommendation);

        // Pengeluaran hari ini jam 1 siang: Rp 20.000
        Carbon::setTestNow('2026-10-11 13:00:00');
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 20000,
            'transaction_date' => '2026-10-11',
            'description' => 'Hari ini siang',
        ]);

        // Batas harian hari ini HARUS TETAP 26.667 (tidak berubah di tengah hari)
        $this->assertEquals(26667, $budget->fresh()->daily_remaining_recommendation);

        // Keesokan harinya (12 Oct jam 08:00):
        Carbon::setTestNow('2026-10-12 08:00:00');

        // Sisa hari: 14 hari. Sisa saldo: 450.000 - 70.000 = 380.000.
        // Batas aman baru: 380.000 / 14 = 27.143
        $this->assertEquals(14, $budget->fresh()->days_remaining);
        $this->assertEquals(27143, $budget->fresh()->daily_remaining_recommendation);

        Carbon::setTestNow();
    }
}
