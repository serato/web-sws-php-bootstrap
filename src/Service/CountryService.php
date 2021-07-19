<?php

namespace Serato\SwsApp\Service;

/**
 * Class CountryService
 * @package App\Service
 */
class CountryService implements CountryServiceInterface
{
    /**
     * @param string $countryCode
     * @return string|null
     */
    public static function getCountryNameWithCountryCode(string $countryCode): ?string
    {
        $countryCode = static::sanitizeString($countryCode);
        return static::COUNTRIES[$countryCode] ?? null;
    }

    /**
     * This method is a temporary solution.
     * We should refactor our database to store country code instead of country name.
     *
     * @param string $countryName
     * @return string|null
     */
    public static function getCountryCodeWithCountryName(string $countryName): ?string
    {
        $countries   = static::sanitizeArrayOfStrings(static::COUNTRIES);
        $countries   = array_flip($countries);
        $countryName = static::sanitizeString($countryName);

        return $countries[$countryName] ?? null;
    }

    /**
     * @return string[]
     */
    public static function getCountries(): array
    {
        return static::COUNTRIES;
    }

    /**
     * Retrieves the code for a given region of the specified country
     *
     * @param string $countryCode
     * @param string $regionName
     * @return string
     */
    public static function getCountryRegionCode(string $countryCode, string $regionName): ?string
    {
        $countryCode = static::sanitizeString($countryCode);
        $regionName  = static::sanitizeString($regionName);

        // several US armed forces locations have the same region code
        $armedForcesEurope = static::sanitizeArrayOfStrings(static::ARMED_FORCES_EUROPE);
        if (in_array($regionName, $armedForcesEurope)) {
            return 'AE';
        }

        // If the country code is invalid return null
        if (!array_key_exists($countryCode, static::COUNTRIES)) {
            return null;
        }

        // If the country does not have regions, return null
        if (!array_key_exists($countryCode, static::REGION)) {
            return null;
        }

        // Get the array containing regions and codes
        $countryRegions = static::sanitizeArrayOfStrings(static::REGION[$countryCode]);
        $countryRegions = array_flip($countryRegions);

        // If the region name is invalid, return null
        if (!array_key_exists($regionName, $countryRegions)) {
            return null;
        }

        return $countryRegions[$regionName];
    }

    /**
     * @param array $arrayToSanitize
     * @return array
     */
    protected static function sanitizeArrayOfStrings(array $arrayToSanitize): array
    {
        return array_map(function (string $value) {
            return static::sanitizeString($value);
        }, $arrayToSanitize);
    }

    /**
     * @param string $countryName
     * @return string
     */
    protected static function sanitizeString(string $countryName): string
    {
        $countryName = str_replace(' ', '', $countryName);
        return strtoupper($countryName);
    }
}
