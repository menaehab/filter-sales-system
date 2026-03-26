<?php

namespace App\Enums;

enum SaleStatusEnum: string
{
    case PAID = 'paid';
    case PARTIAL = 'partial';
    case UNPAID = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::PAID => __('keywords.paid'),
            self::PARTIAL => __('keywords.partial'),
            self::UNPAID => __('keywords.unpaid'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function color(): string
    {
        return match ($this) {
            self::PAID => 'success',
            self::PARTIAL => 'warning',
            self::UNPAID => 'danger',
        };
    }
}
