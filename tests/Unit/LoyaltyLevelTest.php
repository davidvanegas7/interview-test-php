<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Enum\LoyaltyLevel;
use PHPUnit\Framework\TestCase;

class LoyaltyLevelTest extends TestCase
{
    public function testFromAmountBronze(): void
    {
        $this->assertSame(LoyaltyLevel::BRONZE, LoyaltyLevel::fromAmount(0.0));
        $this->assertSame(LoyaltyLevel::BRONZE, LoyaltyLevel::fromAmount(50.0));
        $this->assertSame(LoyaltyLevel::BRONZE, LoyaltyLevel::fromAmount(99.99));
    }

    public function testFromAmountSilver(): void
    {
        $this->assertSame(LoyaltyLevel::SILVER, LoyaltyLevel::fromAmount(100.0));
        $this->assertSame(LoyaltyLevel::SILVER, LoyaltyLevel::fromAmount(250.0));
        $this->assertSame(LoyaltyLevel::SILVER, LoyaltyLevel::fromAmount(500.0));
    }

    public function testFromAmountGold(): void
    {
        $this->assertSame(LoyaltyLevel::GOLD, LoyaltyLevel::fromAmount(500.01));
        $this->assertSame(LoyaltyLevel::GOLD, LoyaltyLevel::fromAmount(1000.0));
        $this->assertSame(LoyaltyLevel::GOLD, LoyaltyLevel::fromAmount(9999.99));
    }

    public function testEnumValues(): void
    {
        $this->assertSame('Bronze', LoyaltyLevel::BRONZE->value);
        $this->assertSame('Silver', LoyaltyLevel::SILVER->value);
        $this->assertSame('Gold', LoyaltyLevel::GOLD->value);
    }
}
