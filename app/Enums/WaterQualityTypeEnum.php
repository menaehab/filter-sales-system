<?php

namespace App\Enums;

enum WaterQualityTypeEnum: string
{
    case GOOD = 'good';
    case FAIR = 'fair';
    case POOR = 'poor';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'good',
            self::FAIR => 'fair',
            self::POOR => 'poor',
        };
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
