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

    public function values()
    {
        return array_column(self::cases(), 'value');
    }
}
