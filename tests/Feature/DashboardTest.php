<?php

namespace Tests\Feature;

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
        $category = $user->categories()->create([
            'name' => 'Makan',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#f97316',
        ]);

        // Transaksi kemarin
        $user->transactions()->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-10-13',
            'description' => 'Makan kemarin',
        ]);

        // Transaksi hari ini
        $user->transactions()->create([
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

    public function test_dashboard_isolates_data_between_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = $userA->categories()->create([
            'name' => 'Gaji A',
            'type' => 'income',
            'icon' => 'wallet',
            'color' => '#10b981',
        ]);

        $userA->transactions()->create([
            'category_id' => $catA->id,
            'type' => 'income',
            'amount' => 10000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Gaji User A',
        ]);

        // User B views dashboard, must have 0 balance and not see User A's transaction
        $response = $this->actingAs($userB)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalBalance', 0.0);
        $response->assertDontSee('Gaji User A');
    }

    public function test_dashboard_filters_by_custom_date_range(): void
    {
        Carbon::setTestNow('2026-10-28 10:00:00');

        $user = User::factory()->create();
        $cat = $user->categories()->create([
            'name' => 'Belanja',
            'type' => 'expense',
            'icon' => 'shopping-bag',
            'color' => '#ef4444',
        ]);

        // Transaction on Oct 05
        $user->transactions()->create([
            'category_id' => $cat->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => '2026-10-05',
            'description' => 'Belanja 5 Okt',
        ]);

        // Transaction on Oct 15
        $user->transactions()->create([
            'category_id' => $cat->id,
            'type' => 'expense',
            'amount' => 250000,
            'transaction_date' => '2026-10-15',
            'description' => 'Belanja 15 Okt',
        ]);

        // Transaction on Oct 25
        $user->transactions()->create([
            'category_id' => $cat->id,
            'type' => 'expense',
            'amount' => 500000,
            'transaction_date' => '2026-10-25',
            'description' => 'Belanja 25 Okt',
        ]);

        // Filter range: Oct 10 to Oct 20 (should only capture Oct 15: 250.000)
        $response = $this->actingAs($user)->get(route('dashboard', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('periodExpense', 250000.0);
        $response->assertViewHas('periodBalance', -250000.0);
        $response->assertSee('250.000');

        Carbon::setTestNow();
    }

    public function test_dashboard_filters_by_preset_and_resets(): void
    {
        Carbon::setTestNow('2026-10-28 10:00:00');

        $user = User::factory()->create();

        // 1. Visit with 7_days preset
        $response = $this->actingAs($user)->get(route('dashboard', ['preset' => '7_days']));
        $response->assertStatus(200);
        $response->assertViewHas('preset', '7_days');
        $response->assertViewHas('periodLabel', '7 Hari Terakhir');

        // 2. Visit with reset
        $resetResponse = $this->actingAs($user)->get(route('dashboard', ['reset' => 1]));
        $resetResponse->assertStatus(200);
        $resetResponse->assertViewHas('preset', 'this_month');
        $resetResponse->assertViewHas('periodLabel', 'Bulan Ini');

        Carbon::setTestNow();
    }
}
