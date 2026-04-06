<?php

namespace App\Traits;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPhones
{
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    public function getPhoneAttribute($value): ?string
    {
        if (filled($value)) {
            return (string) $value;
        }

        if ($this->relationLoaded('phones')) {
            return $this->phones->first()?->number;
        }

        return $this->phones()->value('number');
    }

    public function getPhoneNumbersAttribute(): array
    {
        if ($this->relationLoaded('phones')) {
            return $this->phones
                ->pluck('number')
                ->filter(fn ($number) => filled($number))
                ->values()
                ->all();
        }

        return $this->phones()
            ->pluck('number')
            ->filter(fn ($number) => filled($number))
            ->values()
            ->all();
    }

    public function syncPhones(array $phones): void
    {
        $numbers = collect($phones)
            ->map(fn ($phone) => is_array($phone) ? ($phone['number'] ?? null) : $phone)
            ->map(fn ($number) => is_string($number) ? trim($number) : null)
            ->filter(fn ($number) => filled($number))
            ->unique()
            ->values();

        $this->phones()->delete();

        if ($numbers->isNotEmpty()) {
            $this->phones()->createMany(
                $numbers->map(fn (string $number) => ['number' => $number])->all()
            );
        }
    }
}
