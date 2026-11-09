<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar', 'couple_id', 'pairing_code'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->pairing_code)) {
                $user->pairing_code = self::generateUniquePairingCode();
            }
        });
    }

    /**
     * Generate a unique pairing code for couple connection.
     */
    public static function generateUniquePairingCode(): string
    {
        do {
            $code = 'CP-'.strtoupper(Str::random(6));
        } while (self::where('pairing_code', $code)->exists());

        return $code;
    }

    /**
     * Get or generate pairing code for existing user.
     */
    public function getPairingCode(): string
    {
        if ($this->pairing_code) {
            return $this->pairing_code;
        }

        $code = self::generateUniquePairingCode();
        $this->updateQuietly(['pairing_code' => $code]);

        return $code;
    }

    /**
     * @return BelongsTo<Couple, $this>
     */
    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the paired partner user if coupled.
     */
    public function partner(): ?User
    {
        if (! $this->couple_id) {
            return null;
        }

        return self::where('couple_id', $this->couple_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    /**
     * Check if user is currently paired in an active couple.
     */
    public function isCoupled(): bool
    {
        return $this->couple_id !== null && $this->partner() !== null;
    }

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
     * @return HasMany<PushSubscription, $this>
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
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
