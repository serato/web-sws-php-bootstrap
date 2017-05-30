<?php

namespace Serato\SwsApp\Propel;

use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Collection\ObjectCollection;
use SimpleXMLElement;

/**
 * A convenience class for creating a managing databases using the Propel ORM.
 *
 * Includes functionality to:
 *
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


abstract class AbstractBuilder
{
    /**
     * Create a new database and build a schema in it from one or more directories
     * containing Propel schema XML files.
     *
     * Creates a `Propel\Runtime\Connection\ConnectionWrapper` instance and stores
     * this in the Propel service container using `$name` as a key. Thus, multiple
     * databases and connections can be created.
     *
     * @param array    $schemaDirs     An array of directory paths that contain Propel schema files
     * @param string   $dbName         Name of connection
     *
     * @return ConnectionWrapper
     */
    abstract public static function createDatabase(array $schemaDirs, string $dbName = 'default'): ConnectionWrapper;

    /**
     * Import data into the database from a JSON file by providing a model to populate.
     *
     * @param string              $model          The name of the mode to populate with data
     * @param string              $jsonFilePath   Path to JSON file
     * @param ConnectionWrapper   $con            Database connection
     *
     * @return void
     */
    public static function importJsonDataFile(
        string $model,
        string $jsonFilePath,
        ConnectionWrapper $con = null
    ) {
        self::importJsonDataString($model, file_get_contents($jsonFilePath), $con);
    }

    /**
     * Import data into the database.
     *
     * @param string              $model    The name of the mode to populate with data
     * @param string              $json     JSON string
     * @param ConnectionWrapper   $con      Database connection
     *
     * @return void
     */
    public static function importJsonDataString(
        string $model,
        string $json,
        ConnectionWrapper $con = null
    ) {
        $collection = new ObjectCollection();
        $collection->setModel($model);
        $collection->importFrom('JSON', $json);
        $collection->save($con);
    }

    /**
     * Read Propel schema XML files from one or more directories and return
     * the contents of each file as an item in an array
     *
     * @param array $schemaDirs     List of directories to read
     *
     * @return array
     */
    protected static function readSchemaXmlFiles(array $schemaDirs): array
    {
        $schemas = [];
        foreach ($schemaDirs as $schemaDir) {
            foreach (glob(rtrim($schemaDir, '/') . '/*.xml') as $path) {
                $schemas[] = file_get_contents(realpath($path));
            }
        }
        return $schemas;
    }

    /**
     * Merge an array of Propel schema XML strings into a string representation
     * of a single Propel XML schema document.
     *
     * @param array     $schemas    An array of schema XML strings
     * @param string    $dbName     Name of database to create final schema output for
     *
     * @return string   Final schema XML string
     */
    protected static function mergeSchemaXml(array $schemas, string $dbName = 'default'): string
    {
        $xml = '<?xml version="1.0"?><database name="' . $dbName . '" defaultIdMethod="native"
                defaultPhpNamingMethod="underscore">';
        foreach ($schemas as $schema) {
            $database = new SimpleXMLElement($schema);
            $ns = "\\\\" . (string)$database->attributes()['namespace'];
            foreach ($database->table as $table) {
                $table->addAttribute('namespace', $ns);
                $xml .= $table->asXML();
            }
        }
        $xml .= '</database>';
        // We need to strip out the 'sqlType' declarations because these are MySQL
        // specific, and we're creating a SQLite DB.
        // Not sure if this will bite us on the ass or not (eg. in an enum field)
        return preg_replace('/ sqlType=".*?"/', '', $xml);
    }
}
