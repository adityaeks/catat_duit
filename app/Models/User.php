<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<Budget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Seed 14 initial default categories for this user if none exist.
     */
    public function createDefaultCategories(): void
    {
        if ($this->categories()->exists()) {
            return;
        }

        $defaultCategories = [
            // Expenses
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Belanja Harian', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#8b5cf6'],
            ['name' => 'Tagihan & Utilitas', 'type' => 'expense', 'icon' => 'receipt', 'color' => '#ef4444'],
            ['name' => 'Hiburan & Hobi', 'type' => 'expense', 'icon' => 'gamepad-2', 'color' => '#ec4899'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#06b6d4'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#eab308'],
            ['name' => 'Tempat Tinggal', 'type' => 'expense', 'icon' => 'home', 'color' => '#6366f1'],
            ['name' => 'Pengeluaran Lain', 'type' => 'expense', 'icon' => 'layers', 'color' => '#64748b'],

            // Incomes
            ['name' => 'Gaji Utama', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#10b981'],
            ['name' => 'Bonus & THR', 'type' => 'income', 'icon' => 'gift', 'color' => '#14b8a6'],
            ['name' => 'Freelance / Bisnis', 'type' => 'income', 'icon' => 'laptop', 'color' => '#3b82f6'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#059669'],
            ['name' => 'Pemasukan Lain', 'type' => 'income', 'icon' => 'wallet', 'color' => '#64748b'],
        ];

        foreach ($defaultCategories as $category) {
            $this->categories()->create($category);
        }
    }
}
