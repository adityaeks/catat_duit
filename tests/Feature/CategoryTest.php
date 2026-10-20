<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_categories_page(): void
    {
        $user = User::factory()->create();
        $user->createDefaultCategories();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Makanan & Minuman');
        $response->assertSee('Gaji Utama');
    }

    public function test_categories_are_isolated_between_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = $userA->categories()->create([
            'name' => 'Kategori Khusus User A',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#f97316',
        ]);

        $catB = $userB->categories()->create([
            'name' => 'Kategori Rahasia User B',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#3b82f6',
        ]);

        // User A visits index
        $responseA = $this->actingAs($userA)->get(route('categories.index'));
        $responseA->assertSee('Kategori Khusus User A');
        $responseA->assertDontSee('Kategori Rahasia User B');

        // User B visits index
        $responseB = $this->actingAs($userB)->get(route('categories.index'));
        $responseB->assertSee('Kategori Rahasia User B');
        $responseB->assertDontSee('Kategori Khusus User A');
    }

    public function test_user_can_create_new_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Crypto & Saham',
            'type' => 'income',
            'icon' => 'trending-up',
            'color' => '#10b981',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Crypto & Saham',
            'type' => 'income',
        ]);
    }

    public function test_user_can_update_their_own_category(): void
    {
        $user = User::factory()->create();
        $cat = $user->categories()->create([
            'name' => 'Kategori Lama',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#64748b',
        ]);

        $response = $this->actingAs($user)->put(route('categories.update', $cat), [
            'name' => 'Kategori Baru Diperbarui',
            'type' => 'expense',
            'icon' => 'heart',
            'color' => '#ec4899',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $cat->id,
            'name' => 'Kategori Baru Diperbarui',
            'color' => '#ec4899',
        ]);
    }

    public function test_user_cannot_update_or_delete_other_user_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = $userA->categories()->create([
            'name' => 'Kategori A',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#64748b',
        ]);

        // User B tries to update User A's category
        $responseUpdate = $this->actingAs($userB)->put(route('categories.update', $catA), [
            'name' => 'Diretas',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#ef4444',
        ]);
        $responseUpdate->assertStatus(403);

        // User B tries to delete User A's category
        $responseDelete = $this->actingAs($userB)->delete(route('categories.destroy', $catA));
        $responseDelete->assertStatus(403);
    }
}
