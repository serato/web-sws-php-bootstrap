<?php
namespace Serato\SwsApp\Test\Utils;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Utils\CsvFileIterator;

/**
 * Unit tests for Serato\SwsApp\Utils\CsvFileIterator
 */
class CsvFileIteratorTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testFileNotFound()
    {
        $csv = new CsvFileIterator(__DIR__ . '/../resources/nosuchfile.csv');
        $this->assertTrue(true);
    }

    public function testSmokeTest()
    {
        $csv = new CsvFileIterator(__DIR__ . '/../resources/csv.csv');
        $i = 0;
        foreach ($csv as $data) {
            $this->assertEquals(count($data), 5);
            $i++;
        }
        $this->assertEquals($i, 5);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($a, $b, $c, $d, $e)
    {
        $this->assertEquals(($a + $b + $c + $d + $e), 15);
    }

    public function dataProvider()
    {
        return new CsvFileIterator(__DIR__ . '/../resources/csv.csv');
    }
}
