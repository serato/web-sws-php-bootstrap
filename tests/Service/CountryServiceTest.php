<?php

namespace Serato\SwsApp\Test\Service;

use Serato\SwsApp\Service\CountryService;
use PHPUnit\Framework\TestCase;

/**
 * Class CountryServiceTest
 * @package AppTest\App\Service
 */
class CountryServiceTest extends TestCase
{
    /**
     * @dataProvider getCountryNameWithCountryCodeProvider
     *
     * @param string $countryCode
     * @param string $expectedCountryName
     */
    public function testGetCountryNameWithCountryCode(string $countryCode, string $expectedCountryName): void
    {
        $countryName = CountryService::getCountryNameWithCountryCode($countryCode);
        $this->assertSame($expectedCountryName, $countryName);
    }

    public function testGetCountries(): void
    {
        $countries = CountryService::getCountries();
        $this->assertEquals(247, count($countries));
        $this->assertArrayHasKey('US', $countries);
        $this->assertSame($countries['US'], 'United States of America');
    }

    /**
     * @dataProvider getRegionCodeDataProvider
     *
     * @param string $countryCode
     * @param string $regionName
     * @param string|null $regionCode
     */
    public function testGetCountryRegionCode(string $countryCode, string $regionName, ?string $regionCode): void
    {
        $result = CountryService::getCountryRegionCode($countryCode, $regionName);
        $this->assertSame($regionCode, $result);
    }

    /**
     * @dataProvider getGetRegionNameDataProvider
     *
     * @param string $countryCode
     * @param string $regionCode
     * @param string|null $regionName
     */
    public function testGetCountryRegionName(string $countryCode, string $regionCode, ?string $regionName): void
    {
        $result = CountryService::getCountryRegionName($countryCode, $regionCode);
        $this->assertSame($regionName, $result);
    }

    /**
     * @dataProvider getGetCountryCodeWithCountryNameDataProvider
     *
     * @param string $countryName
     * @param string|null $expectedCountryCode
     */
    public function testGetCountryCodeWithCountryName(string $countryName, ?string $expectedCountryCode): void
    {
        $actualCountryCode = CountryService::getCountryCodeWithCountryName($countryName);
        $this->assertSame($expectedCountryCode, $actualCountryCode);
    }

    /**
     * @return \string[][]
     */
    public function getGetRegionNameDataProvider(): array
    {
        return [
            [ // uppercase
                'country_code' => 'US',
                'state_code'   => 'CA',
                'region_name'  => 'California',
            ],
            [ // lowercase
                'country_code' => 'us',
                'state_code'   => 'ca',
                'region_name'  => 'California',
            ],
            [ // not existing country
                'country_code' => 'xx',
                'state_code'   => 'ca',
                'region_name'  => null,
            ],
            [ // not existing state
                'country_code' => 'US',
                'state_code'   => 'xx',
                'region_name'  => null,
            ],
            [ // armed forces
                'country_code' => 'US',
                'state_code'   => 'AE',
                'region_name'  => 'Armed Forces (Africa, Canada, Europe, Middle East)',
            ],
            [ // not real state
                'country_code' => 'US',
                'state_code'   => 'PR',
                'region_name'  => 'Puerto Rico',
            ],
            [ // province of canada
                'country_code' => 'CA',
                'state_code'   => 'MB',
                'region_name'  => 'Manitoba',
            ],
            [ // province of canada
                'country_code' => 'CA',
                'state_code'   => 'PE',
                'region_name'  => 'Prince Edward Island',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getGetCountryCodeWithCountryNameDataProvider(): array
    {
        return [
            [
                'countryName'         => 'Canada',
                'expectedCountryCode' => 'CA',
            ],
            [
                'countryName'         => 'QWERTY',
                'expectedCountryCode' => null,
            ],
            [
                'countryName'         => 'United States of America',
                'expectedCountryCode' => 'US',
            ],
            [
                'countryName'         => 'united states of america',
                'expectedCountryCode' => 'US',
            ],
            [
                'countryName'         => ' united states OF america ',
                'expectedCountryCode' => 'US',
            ],
            [
                'countryName'         => 'UNITED STATES OF AMERICA',
                'expectedCountryCode' => 'US',
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function getRegionCodeDataProvider(): array
    {
        return [
            [// All valid
                'country_code' => 'US',
                'region_name'  => 'California',
                'state_code'   => 'CA',
            ],
            [// Only country code invalid
                'country_code' => 'US12',
                'region_name'  => 'California',
                'state_code'   => null,
            ],
            [// Country code and region name invalid
                'country_code' => 'US12',
                'region_name'  => 'US13',
                'state_code'   => null,
            ],
            [// Only region name invalid
                'country_code' => 'US',
                'region_name'  => 'Jharkhand',
                'state_code'   => null,
            ],
            [// Valid country which has no regions and a valid region
                'country_code' => 'IN',
                'region_name'  => 'California',
                'state_code'   => null,
            ],
            [// Lower case region name
                'country_code' => 'US',
                'region_name'  => 'california',
                'state_code'   => 'CA',
            ],
            [// Upper case region name
                'country_code' => 'US',
                'region_name'  => 'CALIFORNIA',
                'state_code'   => 'CA',
            ],
            [// Lower case country code
                'country_code' => 'us',
                'region_name'  => 'California',
                'state_code'   => 'CA',
            ],
            [// Empty spaces
                'country_code' => ' US ',
                'region_name'  => ' California ',
                'state_code'   => 'CA',
            ],
            [// Mixed case country code
                'country_code' => 'uS',
                'region_name'  => 'District Of Columbia',
                'state_code'   => 'DC',
            ],
            [// Region name with more than 1 word
                'country_code' => 'US',
                'region_name'  => 'California',
                'state_code'   => 'CA',
            ],
            [// Invalid region name but which is a part of a valid region name
                'country_code' => 'US',
                'region_name'  => 'Armed Forces',
                'state_code'   => null,
            ],
            [// Valid country name and region name
                'country_code' => 'US',
                'region_name'  => 'Armed Forces Middle East',
                'state_code'   => 'AE',
            ],
            [// Valid country name and region name
                'country_code' => 'US',
                'region_name'  => 'armed forces europe',
                'state_code'   => 'AE',
            ],
            [// Valid country name and region name
                'country_code' => 'US',
                'region_name'  => 'ARMED FORCES middle EaSt',
                'state_code'   => 'AE',
            ],
            [// Valid country name and region name
                'country_code' => 'US',
                'region_name'  => 'PuErTo RiCo',
                'state_code'   => 'PR',
            ],
            [// Valid country name and region name
                'country_code' => 'US',
                'region_name'  => 'PR',
                'state_code'   => 'PR',
            ],
            [
                'country_code' => 'CA',
                'region_name'  => 'Alberta',
                'state_code'   => 'AB',
            ],
            [
                'country_code' => 'CA',
                'region_name'  => ' AB ',
                'state_code'   => 'AB',
            ],
            [
                'country_code' => 'CA',
                'region_name'  => 'New Brunswick/Nouveau-Brunswick',
                'state_code'   => 'NB',
            ],
            [
                'country_code' => 'CA',
                'region_name'  => 'Newfoundland and Labrador',
                'state_code'   => 'NL',
            ],
            [
                'country_code' => 'CA',
                'region_name'  => ' Newfoundland  AND  Labrador ',
                'state_code'   => 'NL',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCountryNameWithCountryCodeProvider(): array
    {
        return [
            [
                'countryCode' => 'US',
                'countryName' => 'United States of America',
            ],
            [
                'countryCode' => 'us',
                'countryName' => 'United States of America',
            ],
            [
                'countryCode' => 'Us',
                'countryName' => 'United States of America',
            ],
            [
                'countryCode' => 'cA',
                'countryName' => 'Canada',
            ],
            [
                'countryCode' => ' cA ',
                'countryName' => 'Canada',
            ],
        ];
    }
}
