<?php

namespace App\Models;

use App\Enums\WaterQualityTypeEnum;
use App\Traits\HasLogActivity;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class WaterFilter extends Model
{
    use HasLogActivity, HasSlug;

    protected $fillable = [
        'filter_model',
        'slug',
        'address',
        'installed_at',
        'candle_1_replaced_at',
        'candle_2_3_replaced_at',
        'candle_4_replaced_at',
        'candle_5_replaced_at',
        'candle_6_replaced_at',
        'candle_7_replaced_at',
        'customer_id',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'candle_1_replaced_at' => 'date',
        'candle_2_3_replaced_at' => 'date',
        'candle_4_replaced_at' => 'date',
        'candle_5_replaced_at' => 'date',
        'candle_6_replaced_at' => 'date',
        'candle_7_replaced_at' => 'date',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['filter_model', 'address'])
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function readings()
    {
        return $this->hasMany(WaterReading::class);
    }

    public function candleChanges()
    {
        return $this->hasMany(WaterFilterCandleChange::class);
    }

    public function preInstallationReading()
    {
        return $this->readings()->where('before_installment', true)->oldest()->first();
    }

    public function latestReading()
    {
        return $this->readings()->latest()->first();
    }

    public function getCandle1IntervalMonthsAttribute(): int
    {
        $preReading = $this->preInstallationReading();
        if (! $preReading) {
            return 3;
        }

        return match ($preReading->water_quality) {
            WaterQualityTypeEnum::GOOD->value => 3,
            WaterQualityTypeEnum::FAIR->value => 2,
            WaterQualityTypeEnum::POOR->value => 1,
            default => 3,
        };
    }

    public function getCandle1NextDateAttribute(): ?Carbon
    {
        if (! $this->installed_at) {
            return null;
        }

        $baseDate = $this->candle_1_replaced_at ?? $this->installed_at;

        return Carbon::parse($baseDate)->addMonths($this->candle_1_interval_months);
    }

    public function getCandle23NextDateAttribute(): ?Carbon
    {
        if (! $this->installed_at) {
            return null;
        }

        $baseDate = $this->candle_2_3_replaced_at ?? $this->installed_at;

        return Carbon::parse($baseDate)->addMonths(5);
    }

    public function getCandle4NeedsReplacementAttribute(): bool
    {
        $latestReading = $this->latestReading();

        return $latestReading && $latestReading->tds >= 80;
    }

    public function getCandle5NextDateAttribute(): ?Carbon
    {
        if (! $this->installed_at) {
            return null;
        }

        $baseDate = $this->candle_5_replaced_at ?? $this->installed_at;

        return Carbon::parse($baseDate)->addMonths(6);
    }

    public function getCandle6NextDateAttribute(): ?Carbon
    {
        if (! $this->installed_at) {
            return null;
        }

        $baseDate = $this->candle_6_replaced_at ?? $this->installed_at;

        return Carbon::parse($baseDate)->addMonths(8);
    }

    public function getCandle7NextDateAttribute(): ?Carbon
    {
        if (! $this->installed_at) {
            return null;
        }

        $baseDate = $this->candle_7_replaced_at ?? $this->installed_at;

        return Carbon::parse($baseDate)->addMonths(10);
    }

    public function getCandleStatusAttribute(): array
    {
        $now = now();

        return [
            'candle_1' => $this->getCandleStatus($this->candle_1_next_date, $now),
            'candle_2_3' => $this->getCandleStatus($this->candle_2_3_next_date, $now),
            'candle_4' => $this->candle_4_needs_replacement ? 'danger' : 'success',
            'candle_5' => $this->getCandleStatus($this->candle_5_next_date, $now),
            'candle_6' => $this->getCandleStatus($this->candle_6_next_date, $now),
            'candle_7' => $this->getCandleStatus($this->candle_7_next_date, $now),
        ];
    }

    protected function getCandleStatus(?Carbon $nextDate, ?CarbonInterface $now): string
    {
        if (! $nextDate) {
            return 'unknown';
        }

        if ($nextDate->lte($now)) {
            return 'danger';
        }

        if ($nextDate->lte($now->copy()->addDays(14))) {
            return 'warning';
        }

        return 'success';
    }

    public function markCandleReplaced(string $candleType, ?User $user = null): void
    {
        $candleMap = [
            'candle_1' => ['field' => 'candle_1_replaced_at', 'name' => __('keywords.candle_1')],
            'candle_2_3' => ['field' => 'candle_2_3_replaced_at', 'name' => __('keywords.candle_2_3')],
            'candle_4' => ['field' => 'candle_4_replaced_at', 'name' => __('keywords.candle_4')],
            'candle_5' => ['field' => 'candle_5_replaced_at', 'name' => __('keywords.candle_5')],
            'candle_6' => ['field' => 'candle_6_replaced_at', 'name' => __('keywords.candle_6')],
            'candle_7' => ['field' => 'candle_7_replaced_at', 'name' => __('keywords.candle_7')],
        ];

        $candle = $candleMap[$candleType] ?? null;

        if (! $candle) {
            return;
        }

        $replacedAt = now();

        $this->update([$candle['field'] => $replacedAt]);

        $this->candleChanges()->create([
            'user_id' => $user?->id,
            'candle_key' => $candleType,
            'candle_name' => $candle['name'],
            'replaced_at' => $replacedAt,
        ]);

        activity()
            ->performedOn($this)
            ->causedBy($user)
            ->withProperties([
                'candle_key' => $candleType,
                'candle_name' => $candle['name'],
                'replaced_at' => $replacedAt->toDateTimeString(),
            ])
            ->log(__('keywords.activity_mark_filter_candle_replaced'));
    }
}
