<?php

namespace Serato\SwsApp\Test\Propel\Model\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use Serato\SwsApp\Test\Propel\Model\LicenseType;
use Serato\SwsApp\Test\Propel\Model\LicenseTypeQuery;


/**
 * This class defines the structure of the 'product_serial_number_license_types' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class LicenseTypeTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.LicenseTypeTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'product_serial_number_license_types';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Serato\\SwsApp\\Test\\Propel\\Model\\LicenseType';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'LicenseType';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 12;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 12;

    /**
     * the column name for the license_type_id field
     */
    const COL_LICENSE_TYPE_ID = 'product_serial_number_license_types.license_type_id';

    /**
     * the column name for the name field
     */
    const COL_NAME = 'product_serial_number_license_types.name';

    /**
     * the column name for the current field
     */
    const COL_CURRENT = 'product_serial_number_license_types.current';

    /**
     * the column name for the auth_type field
     */
    const COL_AUTH_TYPE = 'product_serial_number_license_types.auth_type';

    /**
     * the column name for the rlm_product_name field
     */
    const COL_RLM_PRODUCT_NAME = 'product_serial_number_license_types.rlm_product_name';

    /**
     * the column name for the rlm_license_version field
     */
    const COL_RLM_LICENSE_VERSION = 'product_serial_number_license_types.rlm_license_version';

    /**
     * the column name for the license_options field
     */
    const COL_LICENSE_OPTIONS = 'product_serial_number_license_types.license_options';

    /**
     * the column name for the host_app_checksum field
     */
    const COL_HOST_APP_CHECKSUM = 'product_serial_number_license_types.host_app_checksum';

    /**
     * the column name for the serial_number_type field
     */
    const COL_SERIAL_NUMBER_TYPE = 'product_serial_number_license_types.serial_number_type';

    /**
     * the column name for the authorization_limit field
     */
    const COL_AUTHORIZATION_LIMIT = 'product_serial_number_license_types.authorization_limit';

    /**
     * the column name for the expires_days field
     */
    const COL_EXPIRES_DAYS = 'product_serial_number_license_types.expires_days';

    /**
     * the column name for the expires_date field
     */
    const COL_EXPIRES_DATE = 'product_serial_number_license_types.expires_date';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Name', 'Current', 'AuthType', 'RlmProductName', 'RlmLicenseVersion', 'Options', 'ClientApplicationChecksum', 'SerialNumberType', 'AuthorizationLimit', 'ExpiresDays', 'ExpiresDate', ),
        self::TYPE_CAMELNAME     => array('id', 'name', 'current', 'authType', 'rlmProductName', 'rlmLicenseVersion', 'options', 'clientApplicationChecksum', 'serialNumberType', 'authorizationLimit', 'expiresDays', 'expiresDate', ),
        self::TYPE_COLNAME       => array(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, LicenseTypeTableMap::COL_NAME, LicenseTypeTableMap::COL_CURRENT, LicenseTypeTableMap::COL_AUTH_TYPE, LicenseTypeTableMap::COL_RLM_PRODUCT_NAME, LicenseTypeTableMap::COL_RLM_LICENSE_VERSION, LicenseTypeTableMap::COL_LICENSE_OPTIONS, LicenseTypeTableMap::COL_HOST_APP_CHECKSUM, LicenseTypeTableMap::COL_SERIAL_NUMBER_TYPE, LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT, LicenseTypeTableMap::COL_EXPIRES_DAYS, LicenseTypeTableMap::COL_EXPIRES_DATE, ),
        self::TYPE_FIELDNAME     => array('license_type_id', 'name', 'current', 'auth_type', 'rlm_product_name', 'rlm_license_version', 'license_options', 'host_app_checksum', 'serial_number_type', 'authorization_limit', 'expires_days', 'expires_date', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Name' => 1, 'Current' => 2, 'AuthType' => 3, 'RlmProductName' => 4, 'RlmLicenseVersion' => 5, 'Options' => 6, 'ClientApplicationChecksum' => 7, 'SerialNumberType' => 8, 'AuthorizationLimit' => 9, 'ExpiresDays' => 10, 'ExpiresDate' => 11, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'name' => 1, 'current' => 2, 'authType' => 3, 'rlmProductName' => 4, 'rlmLicenseVersion' => 5, 'options' => 6, 'clientApplicationChecksum' => 7, 'serialNumberType' => 8, 'authorizationLimit' => 9, 'expiresDays' => 10, 'expiresDate' => 11, ),
        self::TYPE_COLNAME       => array(LicenseTypeTableMap::COL_LICENSE_TYPE_ID => 0, LicenseTypeTableMap::COL_NAME => 1, LicenseTypeTableMap::COL_CURRENT => 2, LicenseTypeTableMap::COL_AUTH_TYPE => 3, LicenseTypeTableMap::COL_RLM_PRODUCT_NAME => 4, LicenseTypeTableMap::COL_RLM_LICENSE_VERSION => 5, LicenseTypeTableMap::COL_LICENSE_OPTIONS => 6, LicenseTypeTableMap::COL_HOST_APP_CHECKSUM => 7, LicenseTypeTableMap::COL_SERIAL_NUMBER_TYPE => 8, LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT => 9, LicenseTypeTableMap::COL_EXPIRES_DAYS => 10, LicenseTypeTableMap::COL_EXPIRES_DATE => 11, ),
        self::TYPE_FIELDNAME     => array('license_type_id' => 0, 'name' => 1, 'current' => 2, 'auth_type' => 3, 'rlm_product_name' => 4, 'rlm_license_version' => 5, 'license_options' => 6, 'host_app_checksum' => 7, 'serial_number_type' => 8, 'authorization_limit' => 9, 'expires_days' => 10, 'expires_date' => 11, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('product_serial_number_license_types');
        $this->setPhpName('LicenseType');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Serato\\SwsApp\\Test\\Propel\\Model\\LicenseType');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('license_type_id', 'Id', 'TINYINT', true, 3, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 50, null);
        $this->addColumn('current', 'Current', 'TINYINT', true, 3, 0);
        $this->addColumn('auth_type', 'AuthType', 'CHAR', true, null, '');
        $this->addColumn('rlm_product_name', 'RlmProductName', 'VARCHAR', true, 20, '');
        $this->addColumn('rlm_license_version', 'RlmLicenseVersion', 'VARCHAR', true, 5, '');
        $this->addColumn('license_options', 'Options', 'VARCHAR', true, 5, '');
        $this->addColumn('host_app_checksum', 'ClientApplicationChecksum', 'BIGINT', true, null, 0);
        $this->addColumn('serial_number_type', 'SerialNumberType', 'CHAR', true, null, 'seratodj');
        $this->addColumn('authorization_limit', 'AuthorizationLimit', 'TINYINT', true, 1, 2);
        $this->addColumn('expires_days', 'ExpiresDays', 'TINYINT', true, 3, 0);
        $this->addColumn('expires_date', 'ExpiresDate', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? LicenseTypeTableMap::CLASS_DEFAULT : LicenseTypeTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (LicenseType object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = LicenseTypeTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = LicenseTypeTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + LicenseTypeTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = LicenseTypeTableMap::OM_CLASS;
            /** @var LicenseType $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            LicenseTypeTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = LicenseTypeTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = LicenseTypeTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var LicenseType $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                LicenseTypeTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_LICENSE_TYPE_ID);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_NAME);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_CURRENT);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_AUTH_TYPE);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_RLM_PRODUCT_NAME);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_RLM_LICENSE_VERSION);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_LICENSE_OPTIONS);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_HOST_APP_CHECKSUM);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_SERIAL_NUMBER_TYPE);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_EXPIRES_DAYS);
            $criteria->addSelectColumn(LicenseTypeTableMap::COL_EXPIRES_DATE);
        } else {
            $criteria->addSelectColumn($alias . '.license_type_id');
            $criteria->addSelectColumn($alias . '.name');
            $criteria->addSelectColumn($alias . '.current');
            $criteria->addSelectColumn($alias . '.auth_type');
            $criteria->addSelectColumn($alias . '.rlm_product_name');
            $criteria->addSelectColumn($alias . '.rlm_license_version');
            $criteria->addSelectColumn($alias . '.license_options');
            $criteria->addSelectColumn($alias . '.host_app_checksum');
            $criteria->addSelectColumn($alias . '.serial_number_type');
            $criteria->addSelectColumn($alias . '.authorization_limit');
            $criteria->addSelectColumn($alias . '.expires_days');
            $criteria->addSelectColumn($alias . '.expires_date');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(LicenseTypeTableMap::DATABASE_NAME)->getTable(LicenseTypeTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(LicenseTypeTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(LicenseTypeTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new LicenseTypeTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a LicenseType or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or LicenseType object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTypeTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Serato\SwsApp\Test\Propel\Model\LicenseType) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(LicenseTypeTableMap::DATABASE_NAME);
            $criteria->add(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, (array) $values, Criteria::IN);
        }

        $query = LicenseTypeQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            LicenseTypeTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                LicenseTypeTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the product_serial_number_license_types table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return LicenseTypeQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a LicenseType or Criteria object.
     *
     * @param mixed               $criteria Criteria or LicenseType object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTypeTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from LicenseType object
        }


        // Set the correct dbName
        $query = LicenseTypeQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // LicenseTypeTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
LicenseTypeTableMap::buildTableMap();
