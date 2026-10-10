<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category_id',
        'amount',
        'start_date',
        'end_date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate spent amount within the budget start and end dates.
     */
    public function getSpentAmountAttribute(): float
    {
        $query = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [
                $this->start_date->format('Y-m-d'),
                $this->end_date->format('Y-m-d'),
            ]);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Calculate remaining amount.
     */
    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->spent_amount);
    }

    /**
     * Calculate percentage spent.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0.0;
        }

        return round(($this->spent_amount / $this->amount) * 100, 1);
    }

    /**
     * Get budget status: 'safe', 'warning', 'danger'.
     */
    public function getStatusAttribute(): string
    {
        if ($this->spent_amount >= $this->amount) {
            return 'danger';
        }

        if ($this->percentage >= 75) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Remaining days in budget period.
     */
    public function getDaysRemainingAttribute(): int
    {
        $today = now()->startOfDay();
        $endDate = $this->end_date->startOfDay();

        if ($today->gt($endDate)) {
            return 0;
        }

        return (int) $today->diffInDays($endDate) + 1;
    }

    /**
     * Daily budget recommendation for remaining days.
     */
    public function getDailyRemainingRecommendationAttribute(): float
    {
        $days = $this->days_remaining;
        $remaining = $this->remaining_amount;

        if ($days <= 0 || $remaining <= 0) {
            return 0.0;
        }

        return round($remaining / $days, 0);
    }

    /**
     * Check if the budget is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        $today = now()->format('Y-m-d');

        return $today >= $this->start_date->format('Y-m-d') && $today <= $this->end_date->format('Y-m-d');
    }
}
