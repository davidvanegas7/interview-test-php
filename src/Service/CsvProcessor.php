<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ValidationException;
use App\Model\UserData;
use Generator;

class CsvProcessor
{
    public function __construct(
        private readonly DataTransformer $dataTransformer
    ) {
    }

    /**
     * Processes a CSV file line by line using generators for memory efficiency
     *
     * @param string $filePath
     * @return Generator<UserData>
     */
    public function processFile(string $filePath): Generator
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Could not open file: {$filePath}");
        }

        try {
            $lineNumber = 0;
            $processedCount = 0;
            $errorCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // Skip header row if present
                if ($lineNumber === 1 && $this->isHeaderRow($row)) {
                    continue;
                }

                try {
                    $userData = $this->dataTransformer->transformCsvRow($row);
                    $processedCount++;
                    yield $userData;
                } catch (ValidationException $e) {
                    $errorCount++;
                    error_log("Error processing line {$lineNumber}: {$e->getMessage()}");

                    continue;
                }
            }

            echo "Processed {$processedCount} valid records, {$errorCount} errors encountered.\n";
        } finally {
            fclose($handle);
        }
    }

    private function isHeaderRow(array $row): bool
    {
        if (count($row) !== 7) {
            return false;
        }

        $expectedHeaders = ['id', 'first_name', 'last_name', 'email', 'signup_date', 'amount_spent', 'country_code'];
        $normalizedRow = array_map('strtolower', array_map('trim', $row));

        return $normalizedRow === $expectedHeaders;
    }

    /**
     * Writes the transformed data to a JSON file in streaming mode
     *
     * @param Generator<UserData> $dataGenerator
     * @param string $outputPath
     */
    public function writeToJson(Generator $dataGenerator, string $outputPath): void
    {
        $handle = fopen($outputPath, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Could not create output file: {$outputPath}");
        }

        try {
            fwrite($handle, "[\n");
            $isFirst = true;

            foreach ($dataGenerator as $userData) {
                if (!$isFirst) {
                    fwrite($handle, ",\n");
                }

                $json = json_encode($userData->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                if ($json === false) {
                    throw new \RuntimeException("Could not encode data to JSON");
                }

                $indentedJson = $this->indentJson($json);
                fwrite($handle, $indentedJson);

                $isFirst = false;
            }

            fwrite($handle, "\n]");
        } finally {
            fclose($handle);
        }
    }

    private function indentJson(string $json): string
    {
        $lines = explode("\n", $json);
        $indentedLines = array_map(fn($line) => '    ' . $line, $lines);
        return implode("\n", $indentedLines);
    }
}
