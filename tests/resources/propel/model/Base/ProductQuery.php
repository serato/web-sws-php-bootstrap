<?php

namespace Serato\SwsApp\Test\Propel\Model\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Serato\SwsApp\Test\Propel\Model\Product as ChildProduct;
use Serato\SwsApp\Test\Propel\Model\ProductQuery as ChildProductQuery;
use Serato\SwsApp\Test\Propel\Model\Map\ProductTableMap;

/**
 * Base class that represents a query for the 'product_serial_numbers' table.
 *
 *
 *
 * @method     ChildProductQuery orderById($order = Criteria::ASC) Order by the serial_number column
 * @method     ChildProductQuery orderByProductTypeId($order = Criteria::ASC) Order by the product_type_id column
 * @method     ChildProductQuery orderByCounter($order = Criteria::ASC) Order by the counter column
 * @method     ChildProductQuery orderByNfr($order = Criteria::ASC) Order by the nfr column
 * @method     ChildProductQuery orderByDeletedFlag($order = Criteria::ASC) Order by the deleted_flag column
 * @method     ChildProductQuery orderByCreatedAt($order = Criteria::ASC) Order by the date_generated column
 * @method     ChildProductQuery orderByCreatedByUserId($order = Criteria::ASC) Order by the user_id_generated column
 * @method     ChildProductQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildProductQuery orderByUserAddedAt($order = Criteria::ASC) Order by the user_date_added column
 * @method     ChildProductQuery orderByLicensesCreatedAt($order = Criteria::ASC) Order by the licenses_created column
 * @method     ChildProductQuery orderByNotes($order = Criteria::ASC) Order by the notes column
 *
 * @method     ChildProductQuery groupById() Group by the serial_number column
 * @method     ChildProductQuery groupByProductTypeId() Group by the product_type_id column
 * @method     ChildProductQuery groupByCounter() Group by the counter column
 * @method     ChildProductQuery groupByNfr() Group by the nfr column
 * @method     ChildProductQuery groupByDeletedFlag() Group by the deleted_flag column
 * @method     ChildProductQuery groupByCreatedAt() Group by the date_generated column
 * @method     ChildProductQuery groupByCreatedByUserId() Group by the user_id_generated column
 * @method     ChildProductQuery groupByUserId() Group by the user_id column
 * @method     ChildProductQuery groupByUserAddedAt() Group by the user_date_added column
 * @method     ChildProductQuery groupByLicensesCreatedAt() Group by the licenses_created column
 * @method     ChildProductQuery groupByNotes() Group by the notes column
 *
 * @method     ChildProductQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildProductQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildProductQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildProductQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildProductQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildProductQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildProduct findOne(ConnectionInterface $con = null) Return the first ChildProduct matching the query
 * @method     ChildProduct findOneOrCreate(ConnectionInterface $con = null) Return the first ChildProduct matching the query, or a new ChildProduct object populated from the query conditions when no match is found
 *
 * @method     ChildProduct findOneById(string $serial_number) Return the first ChildProduct filtered by the serial_number column
 * @method     ChildProduct findOneByProductTypeId(int $product_type_id) Return the first ChildProduct filtered by the product_type_id column
 * @method     ChildProduct findOneByCounter(int $counter) Return the first ChildProduct filtered by the counter column
 * @method     ChildProduct findOneByNfr(int $nfr) Return the first ChildProduct filtered by the nfr column
 * @method     ChildProduct findOneByDeletedFlag(boolean $deleted_flag) Return the first ChildProduct filtered by the deleted_flag column
 * @method     ChildProduct findOneByCreatedAt(string $date_generated) Return the first ChildProduct filtered by the date_generated column
 * @method     ChildProduct findOneByCreatedByUserId(int $user_id_generated) Return the first ChildProduct filtered by the user_id_generated column
 * @method     ChildProduct findOneByUserId(int $user_id) Return the first ChildProduct filtered by the user_id column
 * @method     ChildProduct findOneByUserAddedAt(string $user_date_added) Return the first ChildProduct filtered by the user_date_added column
 * @method     ChildProduct findOneByLicensesCreatedAt(string $licenses_created) Return the first ChildProduct filtered by the licenses_created column
 * @method     ChildProduct findOneByNotes(string $notes) Return the first ChildProduct filtered by the notes column *

 * @method     ChildProduct requirePk($key, ConnectionInterface $con = null) Return the ChildProduct by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOne(ConnectionInterface $con = null) Return the first ChildProduct matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildProduct requireOneById(string $serial_number) Return the first ChildProduct filtered by the serial_number column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByProductTypeId(int $product_type_id) Return the first ChildProduct filtered by the product_type_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByCounter(int $counter) Return the first ChildProduct filtered by the counter column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByNfr(int $nfr) Return the first ChildProduct filtered by the nfr column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByDeletedFlag(boolean $deleted_flag) Return the first ChildProduct filtered by the deleted_flag column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByCreatedAt(string $date_generated) Return the first ChildProduct filtered by the date_generated column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByCreatedByUserId(int $user_id_generated) Return the first ChildProduct filtered by the user_id_generated column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByUserId(int $user_id) Return the first ChildProduct filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByUserAddedAt(string $user_date_added) Return the first ChildProduct filtered by the user_date_added column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByLicensesCreatedAt(string $licenses_created) Return the first ChildProduct filtered by the licenses_created column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProduct requireOneByNotes(string $notes) Return the first ChildProduct filtered by the notes column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildProduct[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildProduct objects based on current ModelCriteria
 * @method     ChildProduct[]|ObjectCollection findById(string $serial_number) Return ChildProduct objects filtered by the serial_number column
 * @method     ChildProduct[]|ObjectCollection findByProductTypeId(int $product_type_id) Return ChildProduct objects filtered by the product_type_id column
 * @method     ChildProduct[]|ObjectCollection findByCounter(int $counter) Return ChildProduct objects filtered by the counter column
 * @method     ChildProduct[]|ObjectCollection findByNfr(int $nfr) Return ChildProduct objects filtered by the nfr column
 * @method     ChildProduct[]|ObjectCollection findByDeletedFlag(boolean $deleted_flag) Return ChildProduct objects filtered by the deleted_flag column
 * @method     ChildProduct[]|ObjectCollection findByCreatedAt(string $date_generated) Return ChildProduct objects filtered by the date_generated column
 * @method     ChildProduct[]|ObjectCollection findByCreatedByUserId(int $user_id_generated) Return ChildProduct objects filtered by the user_id_generated column
 * @method     ChildProduct[]|ObjectCollection findByUserId(int $user_id) Return ChildProduct objects filtered by the user_id column
 * @method     ChildProduct[]|ObjectCollection findByUserAddedAt(string $user_date_added) Return ChildProduct objects filtered by the user_date_added column
 * @method     ChildProduct[]|ObjectCollection findByLicensesCreatedAt(string $licenses_created) Return ChildProduct objects filtered by the licenses_created column
 * @method     ChildProduct[]|ObjectCollection findByNotes(string $notes) Return ChildProduct objects filtered by the notes column
 * @method     ChildProduct[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ProductQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Serato\SwsApp\Test\Propel\Model\Base\ProductQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Serato\\SwsApp\\Test\\Propel\\Model\\Product', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildProductQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildProductQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildProductQuery) {
            return $criteria;
        }
        $query = new ChildProductQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildProduct|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProductTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ProductTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildProduct A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT serial_number, product_type_id, counter, nfr, deleted_flag, date_generated, user_id_generated, user_id, user_date_added, licenses_created, notes FROM product_serial_numbers WHERE serial_number = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildProduct $obj */
            $obj = new ChildProduct();
            $obj->hydrate($row);
            ProductTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildProduct|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ProductTableMap::COL_SERIAL_NUMBER, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ProductTableMap::COL_SERIAL_NUMBER, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the serial_number column
     *
     * Example usage:
     * <code>
     * $query->filterById('fooValue');   // WHERE serial_number = 'fooValue'
     * $query->filterById('%fooValue%', Criteria::LIKE); // WHERE serial_number LIKE '%fooValue%'
     * </code>
     *
     * @param     string $id The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($id)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_SERIAL_NUMBER, $id, $comparison);
    }

    /**
     * Filter the query on the product_type_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductTypeId(1234); // WHERE product_type_id = 1234
     * $query->filterByProductTypeId(array(12, 34)); // WHERE product_type_id IN (12, 34)
     * $query->filterByProductTypeId(array('min' => 12)); // WHERE product_type_id > 12
     * </code>
     *
     * @param     mixed $productTypeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByProductTypeId($productTypeId = null, $comparison = null)
    {
        if (is_array($productTypeId)) {
            $useMinMax = false;
            if (isset($productTypeId['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_PRODUCT_TYPE_ID, $productTypeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productTypeId['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_PRODUCT_TYPE_ID, $productTypeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_PRODUCT_TYPE_ID, $productTypeId, $comparison);
    }

    /**
     * Filter the query on the counter column
     *
     * Example usage:
     * <code>
     * $query->filterByCounter(1234); // WHERE counter = 1234
     * $query->filterByCounter(array(12, 34)); // WHERE counter IN (12, 34)
     * $query->filterByCounter(array('min' => 12)); // WHERE counter > 12
     * </code>
     *
     * @param     mixed $counter The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByCounter($counter = null, $comparison = null)
    {
        if (is_array($counter)) {
            $useMinMax = false;
            if (isset($counter['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_COUNTER, $counter['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($counter['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_COUNTER, $counter['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_COUNTER, $counter, $comparison);
    }

    /**
     * Filter the query on the nfr column
     *
     * Example usage:
     * <code>
     * $query->filterByNfr(1234); // WHERE nfr = 1234
     * $query->filterByNfr(array(12, 34)); // WHERE nfr IN (12, 34)
     * $query->filterByNfr(array('min' => 12)); // WHERE nfr > 12
     * </code>
     *
     * @param     mixed $nfr The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByNfr($nfr = null, $comparison = null)
    {
        if (is_array($nfr)) {
            $useMinMax = false;
            if (isset($nfr['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_NFR, $nfr['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($nfr['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_NFR, $nfr['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_NFR, $nfr, $comparison);
    }

    /**
     * Filter the query on the deleted_flag column
     *
     * Example usage:
     * <code>
     * $query->filterByDeletedFlag(true); // WHERE deleted_flag = true
     * $query->filterByDeletedFlag('yes'); // WHERE deleted_flag = true
     * </code>
     *
     * @param     boolean|string $deletedFlag The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByDeletedFlag($deletedFlag = null, $comparison = null)
    {
        if (is_string($deletedFlag)) {
            $deletedFlag = in_array(strtolower($deletedFlag), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ProductTableMap::COL_DELETED_FLAG, $deletedFlag, $comparison);
    }

    /**
     * Filter the query on the date_generated column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE date_generated = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE date_generated = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE date_generated > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_DATE_GENERATED, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_DATE_GENERATED, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_DATE_GENERATED, $createdAt, $comparison);
    }

    /**
     * Filter the query on the user_id_generated column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedByUserId(1234); // WHERE user_id_generated = 1234
     * $query->filterByCreatedByUserId(array(12, 34)); // WHERE user_id_generated IN (12, 34)
     * $query->filterByCreatedByUserId(array('min' => 12)); // WHERE user_id_generated > 12
     * </code>
     *
     * @param     mixed $createdByUserId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByCreatedByUserId($createdByUserId = null, $comparison = null)
    {
        if (is_array($createdByUserId)) {
            $useMinMax = false;
            if (isset($createdByUserId['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_ID_GENERATED, $createdByUserId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdByUserId['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_ID_GENERATED, $createdByUserId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_USER_ID_GENERATED, $createdByUserId, $comparison);
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id > 12
     * </code>
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the user_date_added column
     *
     * Example usage:
     * <code>
     * $query->filterByUserAddedAt('2011-03-14'); // WHERE user_date_added = '2011-03-14'
     * $query->filterByUserAddedAt('now'); // WHERE user_date_added = '2011-03-14'
     * $query->filterByUserAddedAt(array('max' => 'yesterday')); // WHERE user_date_added > '2011-03-13'
     * </code>
     *
     * @param     mixed $userAddedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByUserAddedAt($userAddedAt = null, $comparison = null)
    {
        if (is_array($userAddedAt)) {
            $useMinMax = false;
            if (isset($userAddedAt['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_DATE_ADDED, $userAddedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userAddedAt['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_USER_DATE_ADDED, $userAddedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_USER_DATE_ADDED, $userAddedAt, $comparison);
    }

    /**
     * Filter the query on the licenses_created column
     *
     * Example usage:
     * <code>
     * $query->filterByLicensesCreatedAt('2011-03-14'); // WHERE licenses_created = '2011-03-14'
     * $query->filterByLicensesCreatedAt('now'); // WHERE licenses_created = '2011-03-14'
     * $query->filterByLicensesCreatedAt(array('max' => 'yesterday')); // WHERE licenses_created > '2011-03-13'
     * </code>
     *
     * @param     mixed $licensesCreatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByLicensesCreatedAt($licensesCreatedAt = null, $comparison = null)
    {
        if (is_array($licensesCreatedAt)) {
            $useMinMax = false;
            if (isset($licensesCreatedAt['min'])) {
                $this->addUsingAlias(ProductTableMap::COL_LICENSES_CREATED, $licensesCreatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($licensesCreatedAt['max'])) {
                $this->addUsingAlias(ProductTableMap::COL_LICENSES_CREATED, $licensesCreatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_LICENSES_CREATED, $licensesCreatedAt, $comparison);
    }

    /**
     * Filter the query on the notes column
     *
     * Example usage:
     * <code>
     * $query->filterByNotes('fooValue');   // WHERE notes = 'fooValue'
     * $query->filterByNotes('%fooValue%', Criteria::LIKE); // WHERE notes LIKE '%fooValue%'
     * </code>
     *
     * @param     string $notes The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function filterByNotes($notes = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($notes)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductTableMap::COL_NOTES, $notes, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildProduct $product Object to remove from the list of results
     *
     * @return $this|ChildProductQuery The current query, for fluid interface
     */
    public function prune($product = null)
    {
        if ($product) {
            $this->addUsingAlias(ProductTableMap::COL_SERIAL_NUMBER, $product->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the product_serial_numbers table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ProductTableMap::clearInstancePool();
            ProductTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ProductTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ProductTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ProductTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // timestampable behavior

    /**
     * Order by create date desc
     *
     * @return     $this|ChildProductQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(ProductTableMap::COL_DATE_GENERATED);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     $this|ChildProductQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(ProductTableMap::COL_DATE_GENERATED, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date asc
     *
     * @return     $this|ChildProductQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(ProductTableMap::COL_DATE_GENERATED);
    }

} // ProductQuery
