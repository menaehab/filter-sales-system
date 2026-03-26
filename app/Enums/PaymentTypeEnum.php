<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case CASH = 'cash';
    case INSTALLMENT = 'installment';

    public function label(): string
    {
        return match ($this) {
            self::CASH => __('keywords.cash'),
            self::INSTALLMENT => __('keywords.installment'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function isInstallment(): bool
    {
        return $this === self::INSTALLMENT;
    }
}
