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
use Serato\SwsApp\Test\Propel\Model\Product;
use Serato\SwsApp\Test\Propel\Model\ProductQuery;


/**
 * This class defines the structure of the 'product_serial_numbers' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class ProductTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.ProductTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'product_serial_numbers';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Serato\\SwsApp\\Test\\Propel\\Model\\Product';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Product';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 11;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 11;

    /**
     * the column name for the serial_number field
     */
    const COL_SERIAL_NUMBER = 'product_serial_numbers.serial_number';

    /**
     * the column name for the product_type_id field
     */
    const COL_PRODUCT_TYPE_ID = 'product_serial_numbers.product_type_id';

    /**
     * the column name for the counter field
     */
    const COL_COUNTER = 'product_serial_numbers.counter';

    /**
     * the column name for the nfr field
     */
    const COL_NFR = 'product_serial_numbers.nfr';

    /**
     * the column name for the deleted_flag field
     */
    const COL_DELETED_FLAG = 'product_serial_numbers.deleted_flag';

    /**
     * the column name for the date_generated field
     */
    const COL_DATE_GENERATED = 'product_serial_numbers.date_generated';

    /**
     * the column name for the user_id_generated field
     */
    const COL_USER_ID_GENERATED = 'product_serial_numbers.user_id_generated';

    /**
     * the column name for the user_id field
     */
    const COL_USER_ID = 'product_serial_numbers.user_id';

    /**
     * the column name for the user_date_added field
     */
    const COL_USER_DATE_ADDED = 'product_serial_numbers.user_date_added';

    /**
     * the column name for the licenses_created field
     */
    const COL_LICENSES_CREATED = 'product_serial_numbers.licenses_created';

    /**
     * the column name for the notes field
     */
    const COL_NOTES = 'product_serial_numbers.notes';

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
        self::TYPE_PHPNAME       => array('Id', 'ProductTypeId', 'Counter', 'Nfr', 'DeletedFlag', 'CreatedAt', 'CreatedByUserId', 'UserId', 'UserAddedAt', 'LicensesCreatedAt', 'Notes', ),
        self::TYPE_CAMELNAME     => array('id', 'productTypeId', 'counter', 'nfr', 'deletedFlag', 'createdAt', 'createdByUserId', 'userId', 'userAddedAt', 'licensesCreatedAt', 'notes', ),
        self::TYPE_COLNAME       => array(ProductTableMap::COL_SERIAL_NUMBER, ProductTableMap::COL_PRODUCT_TYPE_ID, ProductTableMap::COL_COUNTER, ProductTableMap::COL_NFR, ProductTableMap::COL_DELETED_FLAG, ProductTableMap::COL_DATE_GENERATED, ProductTableMap::COL_USER_ID_GENERATED, ProductTableMap::COL_USER_ID, ProductTableMap::COL_USER_DATE_ADDED, ProductTableMap::COL_LICENSES_CREATED, ProductTableMap::COL_NOTES, ),
        self::TYPE_FIELDNAME     => array('serial_number', 'product_type_id', 'counter', 'nfr', 'deleted_flag', 'date_generated', 'user_id_generated', 'user_id', 'user_date_added', 'licenses_created', 'notes', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'ProductTypeId' => 1, 'Counter' => 2, 'Nfr' => 3, 'DeletedFlag' => 4, 'CreatedAt' => 5, 'CreatedByUserId' => 6, 'UserId' => 7, 'UserAddedAt' => 8, 'LicensesCreatedAt' => 9, 'Notes' => 10, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'productTypeId' => 1, 'counter' => 2, 'nfr' => 3, 'deletedFlag' => 4, 'createdAt' => 5, 'createdByUserId' => 6, 'userId' => 7, 'userAddedAt' => 8, 'licensesCreatedAt' => 9, 'notes' => 10, ),
        self::TYPE_COLNAME       => array(ProductTableMap::COL_SERIAL_NUMBER => 0, ProductTableMap::COL_PRODUCT_TYPE_ID => 1, ProductTableMap::COL_COUNTER => 2, ProductTableMap::COL_NFR => 3, ProductTableMap::COL_DELETED_FLAG => 4, ProductTableMap::COL_DATE_GENERATED => 5, ProductTableMap::COL_USER_ID_GENERATED => 6, ProductTableMap::COL_USER_ID => 7, ProductTableMap::COL_USER_DATE_ADDED => 8, ProductTableMap::COL_LICENSES_CREATED => 9, ProductTableMap::COL_NOTES => 10, ),
        self::TYPE_FIELDNAME     => array('serial_number' => 0, 'product_type_id' => 1, 'counter' => 2, 'nfr' => 3, 'deleted_flag' => 4, 'date_generated' => 5, 'user_id_generated' => 6, 'user_id' => 7, 'user_date_added' => 8, 'licenses_created' => 9, 'notes' => 10, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, )
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
        $this->setName('product_serial_numbers');
        $this->setPhpName('Product');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Serato\\SwsApp\\Test\\Propel\\Model\\Product');
        $this->setPackage('');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('serial_number', 'Id', 'VARCHAR', true, 50, '0');
        $this->addColumn('product_type_id', 'ProductTypeId', 'TINYINT', true, 3, 0);
        $this->addColumn('counter', 'Counter', 'INTEGER', true, 10, 0);
        $this->addColumn('nfr', 'Nfr', 'TINYINT', true, 3, 0);
        $this->addColumn('deleted_flag', 'DeletedFlag', 'BOOLEAN', true, 1, false);
        $this->addColumn('date_generated', 'CreatedAt', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
        $this->addColumn('user_id_generated', 'CreatedByUserId', 'INTEGER', true, 10, 0);
        $this->addColumn('user_id', 'UserId', 'INTEGER', true, 10, 0);
        $this->addColumn('user_date_added', 'UserAddedAt', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
        $this->addColumn('licenses_created', 'LicensesCreatedAt', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
        $this->addColumn('notes', 'Notes', 'LONGVARCHAR', true, null, '');
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'timestampable' => array('create_column' => 'date_generated', 'update_column' => 'updated_at', 'disable_created_at' => 'false', 'disable_updated_at' => 'true', ),
        );
    } // getBehaviors()

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
        return (string) $row[
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
        return $withPrefix ? ProductTableMap::CLASS_DEFAULT : ProductTableMap::OM_CLASS;
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
     * @return array           (Product object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = ProductTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ProductTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ProductTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ProductTableMap::OM_CLASS;
            /** @var Product $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ProductTableMap::addInstanceToPool($obj, $key);
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
            $key = ProductTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ProductTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Product $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ProductTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ProductTableMap::COL_SERIAL_NUMBER);
            $criteria->addSelectColumn(ProductTableMap::COL_PRODUCT_TYPE_ID);
            $criteria->addSelectColumn(ProductTableMap::COL_COUNTER);
            $criteria->addSelectColumn(ProductTableMap::COL_NFR);
            $criteria->addSelectColumn(ProductTableMap::COL_DELETED_FLAG);
            $criteria->addSelectColumn(ProductTableMap::COL_DATE_GENERATED);
            $criteria->addSelectColumn(ProductTableMap::COL_USER_ID_GENERATED);
            $criteria->addSelectColumn(ProductTableMap::COL_USER_ID);
            $criteria->addSelectColumn(ProductTableMap::COL_USER_DATE_ADDED);
            $criteria->addSelectColumn(ProductTableMap::COL_LICENSES_CREATED);
            $criteria->addSelectColumn(ProductTableMap::COL_NOTES);
        } else {
            $criteria->addSelectColumn($alias . '.serial_number');
            $criteria->addSelectColumn($alias . '.product_type_id');
            $criteria->addSelectColumn($alias . '.counter');
            $criteria->addSelectColumn($alias . '.nfr');
            $criteria->addSelectColumn($alias . '.deleted_flag');
            $criteria->addSelectColumn($alias . '.date_generated');
            $criteria->addSelectColumn($alias . '.user_id_generated');
            $criteria->addSelectColumn($alias . '.user_id');
            $criteria->addSelectColumn($alias . '.user_date_added');
            $criteria->addSelectColumn($alias . '.licenses_created');
            $criteria->addSelectColumn($alias . '.notes');
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
        return Propel::getServiceContainer()->getDatabaseMap(ProductTableMap::DATABASE_NAME)->getTable(ProductTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(ProductTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(ProductTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new ProductTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Product or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Product object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ProductTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Serato\SwsApp\Test\Propel\Model\Product) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ProductTableMap::DATABASE_NAME);
            $criteria->add(ProductTableMap::COL_SERIAL_NUMBER, (array) $values, Criteria::IN);
        }

        $query = ProductQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ProductTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ProductTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the product_serial_numbers table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return ProductQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Product or Criteria object.
     *
     * @param mixed               $criteria Criteria or Product object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Product object
        }


        // Set the correct dbName
        $query = ProductQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // ProductTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
ProductTableMap::buildTableMap();
