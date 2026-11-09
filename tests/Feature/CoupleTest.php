<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_pairing_code_is_generated_automatically(): void
    {
        $user = User::factory()->create(['pairing_code' => null]);

        $code = $user->getPairingCode();

        $this->assertNotNull($code);
        $this->assertStringStartsWith('CP-', $code);
        $this->assertEquals(9, strlen($code));
    }

    public function test_user_can_connect_with_partner_pairing_code(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $codeB = $userB->getPairingCode();

        $response = $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $codeB,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $userA->refresh();
        $userB->refresh();

        $this->assertTrue($userA->isCoupled());
        $this->assertTrue($userB->isCoupled());
        $this->assertEquals($userA->couple_id, $userB->couple_id);
        $this->assertEquals($userB->id, $userA->partner()->id);
        $this->assertEquals($userA->id, $userB->partner()->id);
    }

    public function test_user_cannot_connect_with_own_code(): void
    {
        $user = User::factory()->create();
        $code = $user->getPairingCode();

        $response = $this->actingAs($user)->post(route('couple.connect'), [
            'pairing_code' => $code,
        ]);

        $response->assertSessionHasErrors('pairing_code');
        $this->assertFalse($user->isCoupled());
    }

    public function test_user_cannot_connect_with_invalid_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('couple.connect'), [
            'pairing_code' => 'INVALID-CODE',
        ]);

        $response->assertSessionHasErrors('pairing_code');
        $this->assertFalse($user->isCoupled());
    }

    public function test_user_cannot_connect_if_already_coupled(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        // Connect A and B
        $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $userB->getPairingCode(),
        ]);

        // A tries to connect to C
        $response = $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $userC->getPairingCode(),
        ]);

        $response->assertSessionHasErrors('pairing_code');
    }

    public function test_user_can_disconnect_from_couple(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $userB->getPairingCode(),
        ]);

        $this->assertTrue($userA->fresh()->isCoupled());

        $response = $this->actingAs($userA)->post(route('couple.disconnect'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $userA->refresh();
        $userB->refresh();

        $this->assertFalse($userA->isCoupled());
        $this->assertFalse($userB->isCoupled());
        $this->assertNull($userA->couple_id);
        $this->assertNull($userB->couple_id);
    }

    public function test_dashboard_displays_couple_financial_breakdown_when_scope_is_couple(): void
    {
        Carbon::setTestNow('2026-11-07 10:00:00');

        $userA = User::factory()->create(['name' => 'Ghalih']);
        $userB = User::factory()->create(['name' => 'Pasangan']);

        // Pair them
        $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $userB->getPairingCode(),
        ]);

        $userA->refresh();
        $userB->refresh();

        $catA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Makan A',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#f97316',
        ]);

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Makan B',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#3b82f6',
        ]);

        $incA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Gaji A',
            'type' => 'income',
            'icon' => 'wallet',
            'color' => '#10b981',
        ]);

        $incB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Gaji B',
            'type' => 'income',
            'icon' => 'wallet',
            'color' => '#10b981',
        ]);

        // Incomes
        Transaction::create([
            'user_id' => $userA->id,
            'category_id' => $incA->id,
            'type' => 'income',
            'amount' => 5000000,
            'transaction_date' => '2026-11-01',
        ]);

        Transaction::create([
            'user_id' => $userB->id,
            'category_id' => $incB->id,
            'type' => 'income',
            'amount' => 3000000,
            'transaction_date' => '2026-11-01',
        ]);

        // Today's Expenses
        Transaction::create([
            'user_id' => $userA->id,
            'category_id' => $catA->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-11-07',
            'description' => 'Makan Siang A',
        ]);

        Transaction::create([
            'user_id' => $userB->id,
            'category_id' => $catB->id,
            'type' => 'expense',
            'amount' => 30000,
            'transaction_date' => '2026-11-07',
            'description' => 'Kopi B',
        ]);

        // Scope Couple
        $responseCouple = $this->actingAs($userA)->get(route('dashboard', ['scope' => 'couple']));

        $responseCouple->assertStatus(200);
        $responseCouple->assertViewHas('scope', 'couple');
        $responseCouple->assertViewHas('isCoupled', true);
        $responseCouple->assertViewHas('periodIncome', 8000000.0);
        $responseCouple->assertViewHas('periodExpense', 80000.0);
        $responseCouple->assertViewHas('todayExpense', 80000.0);
        $responseCouple->assertViewHas('userTodayExpense', 50000.0);
        $responseCouple->assertViewHas('partnerTodayExpense', 30000.0);

        // Scope Personal
        $responsePersonal = $this->actingAs($userA)->get(route('dashboard', ['scope' => 'personal']));

        $responsePersonal->assertStatus(200);
        $responsePersonal->assertViewHas('scope', 'personal');
        $responsePersonal->assertViewHas('periodIncome', 5000000.0);
        $responsePersonal->assertViewHas('periodExpense', 50000.0);
        $responsePersonal->assertViewHas('todayExpense', 50000.0);
    }

    public function test_couple_budget_aggregates_spending_from_both_partners(): void
    {
        Carbon::setTestNow('2026-11-07 10:00:00');

        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);

        // Pair them
        $this->actingAs($userA)->post(route('couple.connect'), [
            'pairing_code' => $userB->getPairingCode(),
        ]);

        $userA->refresh();
        $userB->refresh();

        $catA = Category::create([
            'user_id' => $userA->id,
            'name' => 'Semua Makanan',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#f97316',
        ]);

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Semua Makanan',
            'type' => 'expense',
            'icon' => 'utensils',
            'color' => '#3b82f6',
        ]);

        // Create couple budget
        $budget = Budget::create([
            'user_id' => $userA->id,
            'name' => 'Budget Belanja Couple',
            'amount' => 500000,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'is_couple' => true,
        ]);

        // A spends 100,000
        Transaction::create([
            'user_id' => $userA->id,
            'category_id' => $catA->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => '2026-11-05',
        ]);

        // B spends 150,000
        Transaction::create([
            'user_id' => $userB->id,
            'category_id' => $catB->id,
            'type' => 'expense',
            'amount' => 150000,
            'transaction_date' => '2026-11-07',
        ]);

        $budget->refresh();

        $this->assertEquals(250000.0, $budget->spent_amount);
        $this->assertEquals(250000.0, $budget->remaining_amount);
        $this->assertEquals(50.0, $budget->percentage);
        $this->assertEquals(150000.0, $budget->spent_today);

        // Partner B can view this budget in budgets.index
        $response = $this->actingAs($userB)->get(route('budgets.index'));
        $response->assertStatus(200);
        $response->assertSee('Budget Belanja Couple');
        $response->assertSee('Couple');

        // Partner B can top-up this budget
        $topUpResponse = $this->actingAs($userB)->post(route('budgets.top-up', $budget), [
            'add_amount' => 100000,
        ]);
        $topUpResponse->assertRedirect();
        $this->assertEquals(600000.0, (float) $budget->fresh()->amount);

        // Partner B cannot delete this budget
        $deleteResponse = $this->actingAs($userB)->delete(route('budgets.destroy', $budget));
        $deleteResponse->assertStatus(403);
    }
}
