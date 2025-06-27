<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\LoyaltyLevel;
use DateTimeImmutable;

readonly class UserData
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public DateTimeImmutable $signupDate,
        public float $amountSpent,
        public string $countryCode,
        public string $countryName,
        public LoyaltyLevel $loyaltyLevel
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'signup_date' => $this->signupDate->format('c'),
            'amount_spent' => $this->amountSpent,
            'country_code' => $this->countryCode,
            'country_name' => $this->countryName,
            'loyalty_level' => $this->loyaltyLevel->value
        ];
    }
}
