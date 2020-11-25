<?php
namespace Serato\SwsApp\EventDispatcher\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use GeoIp2\Model\City as GeoIpCityRecord;

class GeoIp2Normalizer
{
    /**
     * Normalizers a `GeoIp2\Model\City` record
     *
     * @param GeoIpCityRecord $record
     * @return array
     */
    public function normalizeCityRecord(GeoIpCityRecord $record): array
    {
        return ['gettin' => 'it'];
    }
}
