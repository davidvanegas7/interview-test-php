<?php

declare(strict_types=1);

namespace App\Enum;

enum LoyaltyLevel: string
{
    case BRONZE = 'Bronze';
    case SILVER = 'Silver';
    case GOLD = 'Gold';

    public static function fromAmount(float $amount): self
    {
        return match (true) {
            $amount < 100 => self::BRONZE,
            $amount <= 500 => self::SILVER,
            default => self::GOLD
        };
    }
}
