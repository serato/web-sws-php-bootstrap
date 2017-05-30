<?php

namespace Serato\SwsApp\Propel\Sqlite\InMemory;

use Serato\SwsApp\Propel\AbstractBuilder;
use PDO;
use Propel\Generator\Util\QuickBuilder;
use Propel\Runtime\Propel;
use Propel\Runtime\Adapter\Pdo\SqliteAdapter;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Runtime\Connection\ConnectionWrapper;

/**
 * A convenience class for creating a managing an SQLite in-memory database
 * use the Propel ORM.
 *
 * Includes functionality to:
 *
 *      Create an in-memory SQLite database
 *      Build a database schema from one or more Propel schema files
 *      Import data into tables
 *
 * The functionality here is fundamentally similar to that provided by the
 * `Propel\Generator\Util\QuickBuilder::buildSchema` method. But unlike
 * `QuickBuilder::buildSchema` this functionality does not build model classes.
 * ie. It pre-supposes that models already exist for the given database schema(s).
 *
 * With that in mind, this functionality is primarily intended for use within unit
 * and integration tests because it allows for easy setup and teardown of a database
 * schema and/or database data which can then be used for testing database behaviour
 * within an application.
 */
class Builder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    public static function createDatabase(array $schemaDirs, string $dbName = 'default'): ConnectionWrapper
    {
        // Note: QuickBuilder has a static method QuickBuilder::buildSchema which
        // does pretty much the same thing as this function. Except it also
        // generates model class files (using the QuickBuilder::buildClasses method)
        // and includes these files into the current namespace making it impossible
        // to test our own model classes.
        $builder = new QuickBuilder;
        $builder->setSchema(self::mergeSchemaXml(self::readSchemaXmlFiles($schemaDirs), $dbName));
        $adapter = new SqliteAdapter();
        $connection = new ConnectionWrapper(
            new PdoConnection('sqlite::memory:', null, null)
        );
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $adapter->initConnection($connection, []);
        $builder->buildSQL($connection);
        Propel::getServiceContainer()->setAdapter($dbName, $adapter);
        Propel::getServiceContainer()->setConnection($dbName, $connection);

        return $connection;
    }
}
