<?php
namespace Serato\SwsApp\Test\Propel\Sqlite\InMemory;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Propel\Sqlite\InMemory\Builder;
use Serato\SwsApp\Test\Propel\Model\LicenseTypeQuery;
use Serato\SwsApp\Test\Propel\Model\LicenseQuery;
use Serato\SwsApp\Test\Propel\Model\License;

/**
 * Unit tests for Serato\SwsApp\Propel\Sqlite\InMemory\Builder
 */
class BuilderTest extends TestCase
{
    const SCHEMA_DIR            = __DIR__ . '/../../../resources/propel/schemas';
    const JSON_DATA_DIR         = __DIR__ . '/' . '../../../resources/propel/data';
    const MODEL_NAMESPACE       = '\Serato\SwsApp\Test\Propel\Model';
    const COMMON_TABLE_NAMES    = ['LicenseType', 'ProductType'];

    public function testCreateDb()
    {
        $conns = [];
        // Create two databases/connections
        $conns['conn1'] = Builder::createDatabase([self::SCHEMA_DIR], 'conn1');
        $conns['conn2'] = Builder::createDatabase([self::SCHEMA_DIR], 'conn2');
        
        // Import data, but only into one database
        foreach (self::COMMON_TABLE_NAMES as $name) {
            Builder::importJsonDataFile(
                self::MODEL_NAMESPACE . '\\' . $name,
                self::JSON_DATA_DIR . '/' . $name . '.json',
                $conns['conn1']
            );
        }

        $licenseTypeQuery1 = new LicenseTypeQuery('conn1');
        $licenseTypeQuery2 = new LicenseTypeQuery('conn2');

        $this->assertTrue(count($licenseTypeQuery1->find()) > 0);
        $this->assertEquals(count($licenseTypeQuery2->find()), 0);
    }

    public function testReadWriteDefaultDb()
    {
        Builder::createDatabase([self::SCHEMA_DIR]);
        
        foreach (self::COMMON_TABLE_NAMES as $name) {
            Builder::importJsonDataFile(
                self::MODEL_NAMESPACE . '\\' . $name,
                self::JSON_DATA_DIR . '/' . $name . '.json'
            );
        }

        $this->assertTrue(count(LicenseTypeQuery::create()->find()) > 0);
        LicenseTypeQuery::create()->deleteAll();
        $this->assertEquals(count(LicenseTypeQuery::create()->find()), 0);

        $this->assertEquals(count(LicenseQuery::create()->find()), 0);
        $license = new License;
        $license->setId('1223');
        $license->save();
        $this->assertEquals(count(LicenseQuery::create()->find()), 1);
    }
}
