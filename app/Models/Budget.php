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
        'user_id',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            ->whereDate('transaction_date', '>=', $this->start_date->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $this->end_date->format('Y-m-d'));

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

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
     * Calculate spent amount before today within this budget period.
     */
    public function getSpentBeforeTodayAttribute(): float
    {
        $today = now()->format('Y-m-d');
        $startDate = $this->start_date->format('Y-m-d');

        if ($today <= $startDate) {
            return 0.0;
        }

        $query = Transaction::where('type', 'expense')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<', $today);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Calculate spent amount specifically today within this budget.
     */
    public function getSpentTodayAttribute(): float
    {
        $today = now()->format('Y-m-d');

        if ($today < $this->start_date->format('Y-m-d') || $today > $this->end_date->format('Y-m-d')) {
            return 0.0;
        }

        $query = Transaction::where('type', 'expense')
            ->whereDate('transaction_date', $today);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Remaining days in budget period.
     */
    public function getDaysRemainingAttribute(): int
    {
        $today = now()->startOfDay();
        $startDate = $this->start_date->startOfDay();
        $endDate = $this->end_date->startOfDay();

        if ($today->gt($endDate)) {
            return 0;
        }

        $effectiveStart = $today->lt($startDate) ? $startDate : $today;

        return (int) $effectiveStart->diffInDays($endDate) + 1;
    }

    /**
     * Daily budget recommendation for remaining days.
     * Locked for the current day so intraday expenses do not fluctuate the target,
     * and automatically recalculates on the next day based on remaining balance.
     */
    public function getDailyRemainingRecommendationAttribute(): float
    {
        $days = $this->days_remaining;

        if ($days <= 0 || $this->remaining_amount <= 0) {
            return 0.0;
        }

        // Sisa anggaran di awal hari ini (sebelum pengeluaran hari ini)
        $remainingAtStartOfDay = (float) ($this->amount - $this->spent_before_today);

        if ($remainingAtStartOfDay <= 0) {
            return 0.0;
        }

        return round($remainingAtStartOfDay / $days, 0);
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
