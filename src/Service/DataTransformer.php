<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\LoyaltyLevel;
use App\Exception\ValidationException;
use App\Model\UserData;
use DateTimeImmutable;
use DateTimeInterface;

class DataTransformer
{
    public function __construct(
        private readonly CountryService $countryService
    ) {
    }

    /**
     * @param array<string> $csvRow
     * @throws ValidationException
     */
    public function transformCsvRow(array $csvRow): UserData
    {
        if (count($csvRow) !== 7) {
            throw new ValidationException('Invalid CSV row format', $csvRow);
        }

        [$id, $firstName, $lastName, $email, $signupDate, $amountSpent, $countryCode] = $csvRow;

        // Validate and transform ID
        $id = $this->validateId($id);

        // Normalize names
        $firstName = $this->normalizeName($firstName);
        $lastName = $this->normalizeName($lastName);

        // Validate email
        $email = $this->validateEmail($email);

        // Transform signup date
        $signupDate = $this->transformSignupDate($signupDate);

        // Transform amount spent
        $amountSpent = $this->transformAmountSpent($amountSpent);

        // Validate minimum amount
        if ($amountSpent < 10.0) {
            throw new ValidationException('Amount spent is below minimum', $csvRow);
        }

        // Map country code to country name
        $countryCode = strtoupper(trim($countryCode));
        $countryName = $this->countryService->getCountryName($countryCode);

        if ($countryName === null) {
            throw new ValidationException('Invalid country code', $csvRow);
        }

        // Determine loyalty level
        $loyaltyLevel = LoyaltyLevel::fromAmount($amountSpent);

        return new UserData(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            signupDate: $signupDate,
            amountSpent: $amountSpent,
            countryCode: $countryCode,
            countryName: $countryName,
            loyaltyLevel: $loyaltyLevel
        );
    }

    private function validateId(string $id): int
    {
        $id = trim($id);
        if (!is_numeric($id) || (int)$id <= 0) {
            throw new ValidationException('Invalid ID format');
        }

        return (int)$id;
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);
        if (empty($name)) {
            throw new ValidationException('Name cannot be empty');
        }

        return ucfirst(strtolower($name));
    }

    private function validateEmail(string $email): string
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        return strtolower($email);
    }

    private function transformSignupDate(string $signupDate): DateTimeImmutable
    {
        $signupDate = trim($signupDate);

        try {
            $date = new DateTimeImmutable($signupDate);

            return $date->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            throw new ValidationException('Invalid date format', ['date' => $signupDate]);
        }
    }

    private function transformAmountSpent(string $amountSpent): float
    {
        $amountSpent = trim($amountSpent);
        if (!is_numeric($amountSpent)) {
            throw new ValidationException('Invalid amount format');
        }

        $amount = (float)$amountSpent;
        if ($amount < 0) {
            throw new ValidationException('Amount cannot be negative');
        }

        return round($amount, 2);
    }
}
