<?php

declare(strict_types=1);

namespace App\Service;

use WeakMap;

interface CountryServiceInterface
{
    public function getCountryName(string $countryCode): ?string;
}

class CountryService implements CountryServiceInterface
{
    private static WeakMap $instanceCache;
    private ?array $countries = null;

    public function __construct(
        private readonly string $countriesJsonPath = 'countries.json'
    ) {
        if (!isset(self::$instanceCache)) {
            self::$instanceCache = new WeakMap();
        }
    }

    public function getCountryName(string $countryCode): ?string
    {
        if (!isset(self::$instanceCache[$this])) {
            $this->loadCountriesForInstance();
        }

        $countries = self::$instanceCache[$this];
        return $countries[strtoupper($countryCode)] ?? null;
    }

    private function loadCountriesForInstance(): void
    {
        if (!file_exists($this->countriesJsonPath)) {
            throw new \RuntimeException("Countries file not found: {$this->countriesJsonPath}");
        }

        $content = file_get_contents($this->countriesJsonPath);
        if ($content === false) {
            throw new \RuntimeException("Could not read countries file: {$this->countriesJsonPath}");
        }

        $countries = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($countries)) {
            throw new \RuntimeException("Invalid countries file format");
        }

        self::$instanceCache[$this] = $countries;
    }

    /**
     * Method to explicitly clear the cache for this instance
     * Useful for testing or when data needs to be reloaded
     */
    public function clearCache(): void
    {
        if (isset(self::$instanceCache[$this])) {
            unset(self::$instanceCache[$this]);
        }
    }

    /**
     * Checks if this instance has cached data
     */
    public function isCached(): bool
    {
        return isset(self::$instanceCache[$this]);
    }

    /**
     * Gets information about the global cache (useful for debugging)
     */
    public static function getCacheInfo(): array
    {
        $count = 0;
        $instances = [];

        return [
            'cache_enabled' => isset(self::$instanceCache),
            'estimated_cached_instances' => $count
        ];
    }
}
