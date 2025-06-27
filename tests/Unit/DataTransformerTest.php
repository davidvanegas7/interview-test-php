<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Enum\LoyaltyLevel;
use App\Exception\ValidationException;
use App\Service\CountryService;
use App\Service\DataTransformer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DataTransformerTest extends TestCase
{
    private DataTransformer $dataTransformer;
    private CountryService&MockObject $countryService;

    protected function setUp(): void
    {
        $this->countryService = $this->createMock(CountryService::class);
        $this->dataTransformer = new DataTransformer($this->countryService);
    }

    public function testTransformValidCsvRow(): void
    {
        $csvRow = ['1234', 'john', 'doe', 'john@example.com', '2023-11-15', '250.50', 'US'];

        $this->countryService
            ->expects($this->once())
            ->method('getCountryName')
            ->with('US')
            ->willReturn('United States');

        $result = $this->dataTransformer->transformCsvRow($csvRow);

        $this->assertSame(1234, $result->id);
        $this->assertSame('John', $result->firstName);
        $this->assertSame('Doe', $result->lastName);
        $this->assertSame('john@example.com', $result->email);
        $this->assertSame(250.50, $result->amountSpent);
        $this->assertSame('US', $result->countryCode);
        $this->assertSame('United States', $result->countryName);
        $this->assertSame(LoyaltyLevel::SILVER, $result->loyaltyLevel);
    }

    public function testTransformCsvRowWithInvalidEmail(): void
    {
        $csvRow = ['1234', 'John', 'Doe', 'invalid-email', '2023-11-15', '250.50', 'US'];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');

        $this->dataTransformer->transformCsvRow($csvRow);
    }

    public function testTransformCsvRowWithAmountBelowMinimum(): void
    {
        $csvRow = ['1234', 'John', 'Doe', 'john@example.com', '2023-11-15', '5.00', 'US'];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount spent is below minimum');

        $this->dataTransformer->transformCsvRow($csvRow);
    }

    public function testTransformCsvRowWithInvalidCountryCode(): void
    {
        $csvRow = ['1234', 'John', 'Doe', 'john@example.com', '2023-11-15', '250.50', 'XX'];

        $this->countryService
            ->expects($this->once())
            ->method('getCountryName')
            ->with('XX')
            ->willReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid country code');

        $this->dataTransformer->transformCsvRow($csvRow);
    }

    public function testTransformCsvRowWithInvalidRowFormat(): void
    {
        $csvRow = ['1234', 'John', 'Doe']; // Missing columns

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid CSV row format');

        $this->dataTransformer->transformCsvRow($csvRow);
    }

    public function testLoyaltyLevelDetermination(): void
    {
        $testCases = [
            ['50.00', LoyaltyLevel::BRONZE],
            ['99.99', LoyaltyLevel::BRONZE],
            ['100.00', LoyaltyLevel::SILVER],
            ['500.00', LoyaltyLevel::SILVER],
            ['500.01', LoyaltyLevel::GOLD],
            ['1000.00', LoyaltyLevel::GOLD],
        ];

        foreach ($testCases as [$amount, $expectedLevel]) {
            $csvRow = ['1234', 'John', 'Doe', 'john@example.com', '2023-11-15', $amount, 'US'];

            $this->countryService
                ->method('getCountryName')
                ->with('US')
                ->willReturn('United States');

            $result = $this->dataTransformer->transformCsvRow($csvRow);
            $this->assertSame($expectedLevel, $result->loyaltyLevel, "Failed for amount: {$amount}");
        }
    }

    public function testNameNormalization(): void
    {
        $testCases = [
            ['  john  ', 'John'],
            ['MARY', 'Mary'],
            ['anne-marie', 'Anne-marie'],
            ['O\'CONNOR', 'O\'connor'],
        ];

        foreach ($testCases as [$input, $expected]) {
            $csvRow = ['1234', $input, 'Doe', 'john@example.com', '2023-11-15', '100.00', 'US'];

            $this->countryService
                ->method('getCountryName')
                ->with('US')
                ->willReturn('United States');

            $result = $this->dataTransformer->transformCsvRow($csvRow);
            $this->assertSame($expected, $result->firstName, "Failed for input: '{$input}'");
        }
    }

    public function testDateTransformation(): void
    {
        $csvRow = ['1234', 'John', 'Doe', 'john@example.com', '2023-11-15T10:30:00Z', '100.00', 'US'];

        $this->countryService
            ->method('getCountryName')
            ->with('US')
            ->willReturn('United States');

        $result = $this->dataTransformer->transformCsvRow($csvRow);

        $this->assertSame('2023-11-15T10:30:00+00:00', $result->signupDate->format('c'));
        $this->assertSame('UTC', $result->signupDate->getTimezone()->getName());
    }
}
