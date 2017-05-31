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
use Serato\SwsApp\Test\Propel\Model\License as ChildLicense;
use Serato\SwsApp\Test\Propel\Model\LicenseQuery as ChildLicenseQuery;
use Serato\SwsApp\Test\Propel\Model\Map\LicenseTableMap;

/**
 * Base class that represents a query for the 'product_licenses' table.
 *
 *
 *
 * @method     ChildLicenseQuery orderById($order = Criteria::ASC) Order by the license_serial_number column
 * @method     ChildLicenseQuery orderByProductId($order = Criteria::ASC) Order by the serial_number column
 * @method     ChildLicenseQuery orderByLicenseTypeId($order = Criteria::ASC) Order by the license_type_id column
 * @method     ChildLicenseQuery orderByDateGenerated($order = Criteria::ASC) Order by the date_generated column
 * @method     ChildLicenseQuery orderByAuthorizationLimit($order = Criteria::ASC) Order by the authorization_limit column
 * @method     ChildLicenseQuery orderByBlacklisted($order = Criteria::ASC) Order by the blacklisted column
 * @method     ChildLicenseQuery orderByDeletedFlag($order = Criteria::ASC) Order by the deleted_flag column
 * @method     ChildLicenseQuery orderByExpires($order = Criteria::ASC) Order by the expires column
 * @method     ChildLicenseQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildLicenseQuery orderByUserAddedAt($order = Criteria::ASC) Order by the user_date_added column
 *
 * @method     ChildLicenseQuery groupById() Group by the license_serial_number column
 * @method     ChildLicenseQuery groupByProductId() Group by the serial_number column
 * @method     ChildLicenseQuery groupByLicenseTypeId() Group by the license_type_id column
 * @method     ChildLicenseQuery groupByDateGenerated() Group by the date_generated column
 * @method     ChildLicenseQuery groupByAuthorizationLimit() Group by the authorization_limit column
 * @method     ChildLicenseQuery groupByBlacklisted() Group by the blacklisted column
 * @method     ChildLicenseQuery groupByDeletedFlag() Group by the deleted_flag column
 * @method     ChildLicenseQuery groupByExpires() Group by the expires column
 * @method     ChildLicenseQuery groupByUserId() Group by the user_id column
 * @method     ChildLicenseQuery groupByUserAddedAt() Group by the user_date_added column
 *
 * @method     ChildLicenseQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildLicenseQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildLicenseQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildLicenseQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildLicenseQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildLicenseQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildLicense findOne(ConnectionInterface $con = null) Return the first ChildLicense matching the query
 * @method     ChildLicense findOneOrCreate(ConnectionInterface $con = null) Return the first ChildLicense matching the query, or a new ChildLicense object populated from the query conditions when no match is found
 *
 * @method     ChildLicense findOneById(string $license_serial_number) Return the first ChildLicense filtered by the license_serial_number column
 * @method     ChildLicense findOneByProductId(string $serial_number) Return the first ChildLicense filtered by the serial_number column
 * @method     ChildLicense findOneByLicenseTypeId(int $license_type_id) Return the first ChildLicense filtered by the license_type_id column
 * @method     ChildLicense findOneByDateGenerated(string $date_generated) Return the first ChildLicense filtered by the date_generated column
 * @method     ChildLicense findOneByAuthorizationLimit(int $authorization_limit) Return the first ChildLicense filtered by the authorization_limit column
 * @method     ChildLicense findOneByBlacklisted(int $blacklisted) Return the first ChildLicense filtered by the blacklisted column
 * @method     ChildLicense findOneByDeletedFlag(int $deleted_flag) Return the first ChildLicense filtered by the deleted_flag column
 * @method     ChildLicense findOneByExpires(int $expires) Return the first ChildLicense filtered by the expires column
 * @method     ChildLicense findOneByUserId(int $user_id) Return the first ChildLicense filtered by the user_id column
 * @method     ChildLicense findOneByUserAddedAt(string $user_date_added) Return the first ChildLicense filtered by the user_date_added column *

 * @method     ChildLicense requirePk($key, ConnectionInterface $con = null) Return the ChildLicense by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOne(ConnectionInterface $con = null) Return the first ChildLicense matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildLicense requireOneById(string $license_serial_number) Return the first ChildLicense filtered by the license_serial_number column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByProductId(string $serial_number) Return the first ChildLicense filtered by the serial_number column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByLicenseTypeId(int $license_type_id) Return the first ChildLicense filtered by the license_type_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByDateGenerated(string $date_generated) Return the first ChildLicense filtered by the date_generated column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByAuthorizationLimit(int $authorization_limit) Return the first ChildLicense filtered by the authorization_limit column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByBlacklisted(int $blacklisted) Return the first ChildLicense filtered by the blacklisted column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByDeletedFlag(int $deleted_flag) Return the first ChildLicense filtered by the deleted_flag column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByExpires(int $expires) Return the first ChildLicense filtered by the expires column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByUserId(int $user_id) Return the first ChildLicense filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicense requireOneByUserAddedAt(string $user_date_added) Return the first ChildLicense filtered by the user_date_added column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildLicense[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildLicense objects based on current ModelCriteria
 * @method     ChildLicense[]|ObjectCollection findById(string $license_serial_number) Return ChildLicense objects filtered by the license_serial_number column
 * @method     ChildLicense[]|ObjectCollection findByProductId(string $serial_number) Return ChildLicense objects filtered by the serial_number column
 * @method     ChildLicense[]|ObjectCollection findByLicenseTypeId(int $license_type_id) Return ChildLicense objects filtered by the license_type_id column
 * @method     ChildLicense[]|ObjectCollection findByDateGenerated(string $date_generated) Return ChildLicense objects filtered by the date_generated column
 * @method     ChildLicense[]|ObjectCollection findByAuthorizationLimit(int $authorization_limit) Return ChildLicense objects filtered by the authorization_limit column
 * @method     ChildLicense[]|ObjectCollection findByBlacklisted(int $blacklisted) Return ChildLicense objects filtered by the blacklisted column
 * @method     ChildLicense[]|ObjectCollection findByDeletedFlag(int $deleted_flag) Return ChildLicense objects filtered by the deleted_flag column
 * @method     ChildLicense[]|ObjectCollection findByExpires(int $expires) Return ChildLicense objects filtered by the expires column
 * @method     ChildLicense[]|ObjectCollection findByUserId(int $user_id) Return ChildLicense objects filtered by the user_id column
 * @method     ChildLicense[]|ObjectCollection findByUserAddedAt(string $user_date_added) Return ChildLicense objects filtered by the user_date_added column
 * @method     ChildLicense[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class LicenseQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Serato\SwsApp\Test\Propel\Model\Base\LicenseQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Serato\\SwsApp\\Test\\Propel\\Model\\License', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildLicenseQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildLicenseQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildLicenseQuery) {
            return $criteria;
        }
        $query = new ChildLicenseQuery();
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
     * @return ChildLicense|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(LicenseTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = LicenseTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildLicense A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT license_serial_number, serial_number, license_type_id, date_generated, authorization_limit, blacklisted, deleted_flag, expires, user_id, user_date_added FROM product_licenses WHERE license_serial_number = :p0';
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
            /** @var ChildLicense $obj */
            $obj = new ChildLicense();
            $obj->hydrate($row);
            LicenseTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildLicense|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the license_serial_number column
     *
     * Example usage:
     * <code>
     * $query->filterById('fooValue');   // WHERE license_serial_number = 'fooValue'
     * $query->filterById('%fooValue%', Criteria::LIKE); // WHERE license_serial_number LIKE '%fooValue%'
     * </code>
     *
     * @param     string $id The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($id)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $id, $comparison);
    }

    /**
     * Filter the query on the serial_number column
     *
     * Example usage:
     * <code>
     * $query->filterByProductId('fooValue');   // WHERE serial_number = 'fooValue'
     * $query->filterByProductId('%fooValue%', Criteria::LIKE); // WHERE serial_number LIKE '%fooValue%'
     * </code>
     *
     * @param     string $productId The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByProductId($productId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($productId)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_SERIAL_NUMBER, $productId, $comparison);
    }

    /**
     * Filter the query on the license_type_id column
     *
     * Example usage:
     * <code>
     * $query->filterByLicenseTypeId(1234); // WHERE license_type_id = 1234
     * $query->filterByLicenseTypeId(array(12, 34)); // WHERE license_type_id IN (12, 34)
     * $query->filterByLicenseTypeId(array('min' => 12)); // WHERE license_type_id > 12
     * </code>
     *
     * @param     mixed $licenseTypeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByLicenseTypeId($licenseTypeId = null, $comparison = null)
    {
        if (is_array($licenseTypeId)) {
            $useMinMax = false;
            if (isset($licenseTypeId['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_LICENSE_TYPE_ID, $licenseTypeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($licenseTypeId['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_LICENSE_TYPE_ID, $licenseTypeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_LICENSE_TYPE_ID, $licenseTypeId, $comparison);
    }

    /**
     * Filter the query on the date_generated column
     *
     * Example usage:
     * <code>
     * $query->filterByDateGenerated('2011-03-14'); // WHERE date_generated = '2011-03-14'
     * $query->filterByDateGenerated('now'); // WHERE date_generated = '2011-03-14'
     * $query->filterByDateGenerated(array('max' => 'yesterday')); // WHERE date_generated > '2011-03-13'
     * </code>
     *
     * @param     mixed $dateGenerated The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByDateGenerated($dateGenerated = null, $comparison = null)
    {
        if (is_array($dateGenerated)) {
            $useMinMax = false;
            if (isset($dateGenerated['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_DATE_GENERATED, $dateGenerated['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateGenerated['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_DATE_GENERATED, $dateGenerated['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_DATE_GENERATED, $dateGenerated, $comparison);
    }

    /**
     * Filter the query on the authorization_limit column
     *
     * Example usage:
     * <code>
     * $query->filterByAuthorizationLimit(1234); // WHERE authorization_limit = 1234
     * $query->filterByAuthorizationLimit(array(12, 34)); // WHERE authorization_limit IN (12, 34)
     * $query->filterByAuthorizationLimit(array('min' => 12)); // WHERE authorization_limit > 12
     * </code>
     *
     * @param     mixed $authorizationLimit The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByAuthorizationLimit($authorizationLimit = null, $comparison = null)
    {
        if (is_array($authorizationLimit)) {
            $useMinMax = false;
            if (isset($authorizationLimit['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorizationLimit['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit, $comparison);
    }

    /**
     * Filter the query on the blacklisted column
     *
     * Example usage:
     * <code>
     * $query->filterByBlacklisted(1234); // WHERE blacklisted = 1234
     * $query->filterByBlacklisted(array(12, 34)); // WHERE blacklisted IN (12, 34)
     * $query->filterByBlacklisted(array('min' => 12)); // WHERE blacklisted > 12
     * </code>
     *
     * @param     mixed $blacklisted The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByBlacklisted($blacklisted = null, $comparison = null)
    {
        if (is_array($blacklisted)) {
            $useMinMax = false;
            if (isset($blacklisted['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_BLACKLISTED, $blacklisted['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($blacklisted['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_BLACKLISTED, $blacklisted['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_BLACKLISTED, $blacklisted, $comparison);
    }

    /**
     * Filter the query on the deleted_flag column
     *
     * Example usage:
     * <code>
     * $query->filterByDeletedFlag(1234); // WHERE deleted_flag = 1234
     * $query->filterByDeletedFlag(array(12, 34)); // WHERE deleted_flag IN (12, 34)
     * $query->filterByDeletedFlag(array('min' => 12)); // WHERE deleted_flag > 12
     * </code>
     *
     * @param     mixed $deletedFlag The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByDeletedFlag($deletedFlag = null, $comparison = null)
    {
        if (is_array($deletedFlag)) {
            $useMinMax = false;
            if (isset($deletedFlag['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_DELETED_FLAG, $deletedFlag['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deletedFlag['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_DELETED_FLAG, $deletedFlag['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_DELETED_FLAG, $deletedFlag, $comparison);
    }

    /**
     * Filter the query on the expires column
     *
     * Example usage:
     * <code>
     * $query->filterByExpires(1234); // WHERE expires = 1234
     * $query->filterByExpires(array(12, 34)); // WHERE expires IN (12, 34)
     * $query->filterByExpires(array('min' => 12)); // WHERE expires > 12
     * </code>
     *
     * @param     mixed $expires The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByExpires($expires = null, $comparison = null)
    {
        if (is_array($expires)) {
            $useMinMax = false;
            if (isset($expires['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_EXPIRES, $expires['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expires['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_EXPIRES, $expires['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_EXPIRES, $expires, $comparison);
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
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_USER_ID, $userId, $comparison);
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
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function filterByUserAddedAt($userAddedAt = null, $comparison = null)
    {
        if (is_array($userAddedAt)) {
            $useMinMax = false;
            if (isset($userAddedAt['min'])) {
                $this->addUsingAlias(LicenseTableMap::COL_USER_DATE_ADDED, $userAddedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userAddedAt['max'])) {
                $this->addUsingAlias(LicenseTableMap::COL_USER_DATE_ADDED, $userAddedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTableMap::COL_USER_DATE_ADDED, $userAddedAt, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildLicense $license Object to remove from the list of results
     *
     * @return $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function prune($license = null)
    {
        if ($license) {
            $this->addUsingAlias(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $license->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the product_licenses table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            LicenseTableMap::clearInstancePool();
            LicenseTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(LicenseTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            LicenseTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            LicenseTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // timestampable behavior

    /**
     * Order by create date desc
     *
     * @return     $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(LicenseTableMap::COL_DATE_GENERATED);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(LicenseTableMap::COL_DATE_GENERATED, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date asc
     *
     * @return     $this|ChildLicenseQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(LicenseTableMap::COL_DATE_GENERATED);
    }

} // LicenseQuery
