<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_today_spent_amount_and_count(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');

        $user = User::factory()->create();
        $category = Category::firstOrCreate(
            ['name' => 'Makan', 'type' => 'expense'],
            ['icon' => 'utensils', 'color' => '#f97316']
        );

        // Transaksi kemarin
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-10-13',
            'description' => 'Makan kemarin',
        ]);

        // Transaksi hari ini
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 28000,
            'transaction_date' => '2026-10-14',
            'description' => 'Makan hari ini',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('todayExpense', 28000.0);
        $response->assertViewHas('todayExpenseCount', 1);
        $response->assertSee('Habis Hari Ini');
        $response->assertSee('28.000');
        $response->assertSee('1 transaksi');

        Carbon::setTestNow();
    }

    public function test_dashboard_displays_zero_when_no_expense_today(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('todayExpense', 0.0);
        $response->assertViewHas('todayExpenseCount', 0);
        $response->assertSee('Habis Hari Ini');
        $response->assertSee('Rp 0');
        $response->assertSee('0 transaksi');

        Carbon::setTestNow();
    }
}
