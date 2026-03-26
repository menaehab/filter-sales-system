<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CUSTOMER_CREDIT = 'customer_credit';
    case SUPPLIER_CREDIT = 'supplier_credit';

    public function label(): string
    {
        return match ($this) {
            self::CASH => __('keywords.cash'),
            self::BANK_TRANSFER => __('keywords.bank_transfer'),
            self::CUSTOMER_CREDIT => __('keywords.customer_credit'),
            self::SUPPLIER_CREDIT => __('keywords.supplier_credit'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function customerMethods(): array
    {
        return [self::CASH, self::BANK_TRANSFER, self::CUSTOMER_CREDIT];
    }

    public static function supplierMethods(): array
    {
        return [self::CASH, self::BANK_TRANSFER, self::SUPPLIER_CREDIT];
    }
}
