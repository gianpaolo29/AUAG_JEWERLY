<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\PictureUrl;
use App\Models\PawnPicture;


class PawnItem extends Model
{
    protected $fillable = [
        'customer_id',
        'loan_date',
        'due_date',
        'title',
        'description',
        'price',
        'interest_cost',
        'status',
    ];

    protected $casts = [
        'loan_date'     => 'date',
        'due_date'      => 'date',
        'price'         => 'float',
        'interest_cost' => 'float',
    ];

    // Optional if you ever return PawnItem as JSON and want these included.
    protected $appends = [
        'computed_interest',
        'to_pay',
        'is_overdue',
        'overdue_months',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Months overdue AFTER the due date.
     * - 0 if not yet due
     * - 1 when it's at least 1 full month after due date, etc.
     */
    public function getOverdueMonthsAttribute(): int
    {
        if (! $this->due_date) return 0;

        // If you want to STOP accruing after redeem/forfeit, uncomment:
        // if ($this->status !== 'active') return 0;

        $due = Carbon::parse($this->due_date)->endOfDay();
        $now = now();

        if ($now->lessThanOrEqualTo($due)) {
            return 0;
        }

        // Full months after due date
        return $due->diffInMonths($now);
    }

    /**
     * Interest logic:
     * - Base interest: use saved interest_cost if present, otherwise 3% of principal
     * - Overdue interest: + (principal * 3%) per month after due date
     */
    public function getComputedInterestAttribute(): float
    {
        $principal = (float) ($this->price ?? 0);

        $baseInterest = (float) (
            $this->interest_cost !== null
                ? $this->interest_cost
                : round($principal * 0.03, 2)
        );

        $overdueMonths = (int) ($this->overdue_months ?? 0);
        $overdueInterest = round($principal * 0.03 * $overdueMonths, 2);

        return round($baseInterest + $overdueInterest, 2);
    }

    public function getToPayAttribute(): float
    {
        $principal = (float) ($this->price ?? 0);
        return round($principal + (float) $this->computed_interest, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->due_date) return false;

        // Usually only mark overdue when still active
        if (($this->status ?? '') !== 'active') return false;

        return now()->greaterThan(Carbon::parse($this->due_date)->endOfDay());
    }

    public function pictures()
    {
        return $this->morphMany(PictureUrl::class, 'imageable');
    }

    // 👇 SAME IDEA AS IN Product::getImageUrlAttribute()
    public function getImageUrlAttribute(): string
    {
        // first() because morphMany = multiple records
        $pic = $this->pictures->first();

        if (! $pic || ! $pic->url) {
            return asset('images/placeholder-product.png');
        }

        $path = $pic->url;

        // already full URL
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // stored in storage/app/public/...
        return asset('storage/'.ltrim($path, '/'));
    }

}
