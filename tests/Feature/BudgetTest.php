<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
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
}
