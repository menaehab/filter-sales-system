<?php

namespace App\Enums;

enum WaterFilterFaucetTypeEnum : string
{
    case TAP = 'tap';
    case EASY = 'easy';
    case MIXER = 'mixer';

    public function label(): string
    {
        return match ($this) {
            self::TAP => __('keywords.tap'),
            self::EASY => __('keywords.easy'),
            self::MIXER => __('keywords.mixer'),
        };
    }

    public static function options(): array
    {
        return [
            self::TAP->value => self::TAP->label(),
            self::EASY->value => self::EASY->label(),
            self::MIXER->value => self::MIXER->label(),
        ];
    }
}
