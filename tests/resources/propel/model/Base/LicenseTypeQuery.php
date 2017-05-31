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
use Serato\SwsApp\Test\Propel\Model\LicenseType as ChildLicenseType;
use Serato\SwsApp\Test\Propel\Model\LicenseTypeQuery as ChildLicenseTypeQuery;
use Serato\SwsApp\Test\Propel\Model\Map\LicenseTypeTableMap;

/**
 * Base class that represents a query for the 'product_serial_number_license_types' table.
 *
 *
 *
 * @method     ChildLicenseTypeQuery orderById($order = Criteria::ASC) Order by the license_type_id column
 * @method     ChildLicenseTypeQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildLicenseTypeQuery orderByCurrent($order = Criteria::ASC) Order by the current column
 * @method     ChildLicenseTypeQuery orderByAuthType($order = Criteria::ASC) Order by the auth_type column
 * @method     ChildLicenseTypeQuery orderByRlmProductName($order = Criteria::ASC) Order by the rlm_product_name column
 * @method     ChildLicenseTypeQuery orderByRlmLicenseVersion($order = Criteria::ASC) Order by the rlm_license_version column
 * @method     ChildLicenseTypeQuery orderByOptions($order = Criteria::ASC) Order by the license_options column
 * @method     ChildLicenseTypeQuery orderByClientApplicationChecksum($order = Criteria::ASC) Order by the host_app_checksum column
 * @method     ChildLicenseTypeQuery orderBySerialNumberType($order = Criteria::ASC) Order by the serial_number_type column
 * @method     ChildLicenseTypeQuery orderByAuthorizationLimit($order = Criteria::ASC) Order by the authorization_limit column
 * @method     ChildLicenseTypeQuery orderByExpiresDays($order = Criteria::ASC) Order by the expires_days column
 * @method     ChildLicenseTypeQuery orderByExpiresDate($order = Criteria::ASC) Order by the expires_date column
 *
 * @method     ChildLicenseTypeQuery groupById() Group by the license_type_id column
 * @method     ChildLicenseTypeQuery groupByName() Group by the name column
 * @method     ChildLicenseTypeQuery groupByCurrent() Group by the current column
 * @method     ChildLicenseTypeQuery groupByAuthType() Group by the auth_type column
 * @method     ChildLicenseTypeQuery groupByRlmProductName() Group by the rlm_product_name column
 * @method     ChildLicenseTypeQuery groupByRlmLicenseVersion() Group by the rlm_license_version column
 * @method     ChildLicenseTypeQuery groupByOptions() Group by the license_options column
 * @method     ChildLicenseTypeQuery groupByClientApplicationChecksum() Group by the host_app_checksum column
 * @method     ChildLicenseTypeQuery groupBySerialNumberType() Group by the serial_number_type column
 * @method     ChildLicenseTypeQuery groupByAuthorizationLimit() Group by the authorization_limit column
 * @method     ChildLicenseTypeQuery groupByExpiresDays() Group by the expires_days column
 * @method     ChildLicenseTypeQuery groupByExpiresDate() Group by the expires_date column
 *
 * @method     ChildLicenseTypeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildLicenseTypeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildLicenseTypeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildLicenseTypeQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildLicenseTypeQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildLicenseTypeQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildLicenseType findOne(ConnectionInterface $con = null) Return the first ChildLicenseType matching the query
 * @method     ChildLicenseType findOneOrCreate(ConnectionInterface $con = null) Return the first ChildLicenseType matching the query, or a new ChildLicenseType object populated from the query conditions when no match is found
 *
 * @method     ChildLicenseType findOneById(int $license_type_id) Return the first ChildLicenseType filtered by the license_type_id column
 * @method     ChildLicenseType findOneByName(string $name) Return the first ChildLicenseType filtered by the name column
 * @method     ChildLicenseType findOneByCurrent(int $current) Return the first ChildLicenseType filtered by the current column
 * @method     ChildLicenseType findOneByAuthType(string $auth_type) Return the first ChildLicenseType filtered by the auth_type column
 * @method     ChildLicenseType findOneByRlmProductName(string $rlm_product_name) Return the first ChildLicenseType filtered by the rlm_product_name column
 * @method     ChildLicenseType findOneByRlmLicenseVersion(string $rlm_license_version) Return the first ChildLicenseType filtered by the rlm_license_version column
 * @method     ChildLicenseType findOneByOptions(string $license_options) Return the first ChildLicenseType filtered by the license_options column
 * @method     ChildLicenseType findOneByClientApplicationChecksum(string $host_app_checksum) Return the first ChildLicenseType filtered by the host_app_checksum column
 * @method     ChildLicenseType findOneBySerialNumberType(string $serial_number_type) Return the first ChildLicenseType filtered by the serial_number_type column
 * @method     ChildLicenseType findOneByAuthorizationLimit(int $authorization_limit) Return the first ChildLicenseType filtered by the authorization_limit column
 * @method     ChildLicenseType findOneByExpiresDays(int $expires_days) Return the first ChildLicenseType filtered by the expires_days column
 * @method     ChildLicenseType findOneByExpiresDate(string $expires_date) Return the first ChildLicenseType filtered by the expires_date column *

 * @method     ChildLicenseType requirePk($key, ConnectionInterface $con = null) Return the ChildLicenseType by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOne(ConnectionInterface $con = null) Return the first ChildLicenseType matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildLicenseType requireOneById(int $license_type_id) Return the first ChildLicenseType filtered by the license_type_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByName(string $name) Return the first ChildLicenseType filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByCurrent(int $current) Return the first ChildLicenseType filtered by the current column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByAuthType(string $auth_type) Return the first ChildLicenseType filtered by the auth_type column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByRlmProductName(string $rlm_product_name) Return the first ChildLicenseType filtered by the rlm_product_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByRlmLicenseVersion(string $rlm_license_version) Return the first ChildLicenseType filtered by the rlm_license_version column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByOptions(string $license_options) Return the first ChildLicenseType filtered by the license_options column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByClientApplicationChecksum(string $host_app_checksum) Return the first ChildLicenseType filtered by the host_app_checksum column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneBySerialNumberType(string $serial_number_type) Return the first ChildLicenseType filtered by the serial_number_type column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByAuthorizationLimit(int $authorization_limit) Return the first ChildLicenseType filtered by the authorization_limit column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByExpiresDays(int $expires_days) Return the first ChildLicenseType filtered by the expires_days column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildLicenseType requireOneByExpiresDate(string $expires_date) Return the first ChildLicenseType filtered by the expires_date column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildLicenseType[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildLicenseType objects based on current ModelCriteria
 * @method     ChildLicenseType[]|ObjectCollection findById(int $license_type_id) Return ChildLicenseType objects filtered by the license_type_id column
 * @method     ChildLicenseType[]|ObjectCollection findByName(string $name) Return ChildLicenseType objects filtered by the name column
 * @method     ChildLicenseType[]|ObjectCollection findByCurrent(int $current) Return ChildLicenseType objects filtered by the current column
 * @method     ChildLicenseType[]|ObjectCollection findByAuthType(string $auth_type) Return ChildLicenseType objects filtered by the auth_type column
 * @method     ChildLicenseType[]|ObjectCollection findByRlmProductName(string $rlm_product_name) Return ChildLicenseType objects filtered by the rlm_product_name column
 * @method     ChildLicenseType[]|ObjectCollection findByRlmLicenseVersion(string $rlm_license_version) Return ChildLicenseType objects filtered by the rlm_license_version column
 * @method     ChildLicenseType[]|ObjectCollection findByOptions(string $license_options) Return ChildLicenseType objects filtered by the license_options column
 * @method     ChildLicenseType[]|ObjectCollection findByClientApplicationChecksum(string $host_app_checksum) Return ChildLicenseType objects filtered by the host_app_checksum column
 * @method     ChildLicenseType[]|ObjectCollection findBySerialNumberType(string $serial_number_type) Return ChildLicenseType objects filtered by the serial_number_type column
 * @method     ChildLicenseType[]|ObjectCollection findByAuthorizationLimit(int $authorization_limit) Return ChildLicenseType objects filtered by the authorization_limit column
 * @method     ChildLicenseType[]|ObjectCollection findByExpiresDays(int $expires_days) Return ChildLicenseType objects filtered by the expires_days column
 * @method     ChildLicenseType[]|ObjectCollection findByExpiresDate(string $expires_date) Return ChildLicenseType objects filtered by the expires_date column
 * @method     ChildLicenseType[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class LicenseTypeQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Serato\SwsApp\Test\Propel\Model\Base\LicenseTypeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Serato\\SwsApp\\Test\\Propel\\Model\\LicenseType', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildLicenseTypeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildLicenseTypeQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildLicenseTypeQuery) {
            return $criteria;
        }
        $query = new ChildLicenseTypeQuery();
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
     * @return ChildLicenseType|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(LicenseTypeTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = LicenseTypeTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildLicenseType A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT license_type_id, name, current, auth_type, rlm_product_name, rlm_license_version, license_options, host_app_checksum, serial_number_type, authorization_limit, expires_days, expires_date FROM product_serial_number_license_types WHERE license_type_id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildLicenseType $obj */
            $obj = new ChildLicenseType();
            $obj->hydrate($row);
            LicenseTypeTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildLicenseType|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the license_type_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE license_type_id = 1234
     * $query->filterById(array(12, 34)); // WHERE license_type_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE license_type_id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $id, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the current column
     *
     * Example usage:
     * <code>
     * $query->filterByCurrent(1234); // WHERE current = 1234
     * $query->filterByCurrent(array(12, 34)); // WHERE current IN (12, 34)
     * $query->filterByCurrent(array('min' => 12)); // WHERE current > 12
     * </code>
     *
     * @param     mixed $current The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByCurrent($current = null, $comparison = null)
    {
        if (is_array($current)) {
            $useMinMax = false;
            if (isset($current['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_CURRENT, $current['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($current['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_CURRENT, $current['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_CURRENT, $current, $comparison);
    }

    /**
     * Filter the query on the auth_type column
     *
     * Example usage:
     * <code>
     * $query->filterByAuthType('fooValue');   // WHERE auth_type = 'fooValue'
     * $query->filterByAuthType('%fooValue%', Criteria::LIKE); // WHERE auth_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $authType The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByAuthType($authType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($authType)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_AUTH_TYPE, $authType, $comparison);
    }

    /**
     * Filter the query on the rlm_product_name column
     *
     * Example usage:
     * <code>
     * $query->filterByRlmProductName('fooValue');   // WHERE rlm_product_name = 'fooValue'
     * $query->filterByRlmProductName('%fooValue%', Criteria::LIKE); // WHERE rlm_product_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rlmProductName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByRlmProductName($rlmProductName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rlmProductName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_RLM_PRODUCT_NAME, $rlmProductName, $comparison);
    }

    /**
     * Filter the query on the rlm_license_version column
     *
     * Example usage:
     * <code>
     * $query->filterByRlmLicenseVersion('fooValue');   // WHERE rlm_license_version = 'fooValue'
     * $query->filterByRlmLicenseVersion('%fooValue%', Criteria::LIKE); // WHERE rlm_license_version LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rlmLicenseVersion The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByRlmLicenseVersion($rlmLicenseVersion = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rlmLicenseVersion)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_RLM_LICENSE_VERSION, $rlmLicenseVersion, $comparison);
    }

    /**
     * Filter the query on the license_options column
     *
     * Example usage:
     * <code>
     * $query->filterByOptions('fooValue');   // WHERE license_options = 'fooValue'
     * $query->filterByOptions('%fooValue%', Criteria::LIKE); // WHERE license_options LIKE '%fooValue%'
     * </code>
     *
     * @param     string $options The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByOptions($options = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($options)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_OPTIONS, $options, $comparison);
    }

    /**
     * Filter the query on the host_app_checksum column
     *
     * Example usage:
     * <code>
     * $query->filterByClientApplicationChecksum(1234); // WHERE host_app_checksum = 1234
     * $query->filterByClientApplicationChecksum(array(12, 34)); // WHERE host_app_checksum IN (12, 34)
     * $query->filterByClientApplicationChecksum(array('min' => 12)); // WHERE host_app_checksum > 12
     * </code>
     *
     * @param     mixed $clientApplicationChecksum The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByClientApplicationChecksum($clientApplicationChecksum = null, $comparison = null)
    {
        if (is_array($clientApplicationChecksum)) {
            $useMinMax = false;
            if (isset($clientApplicationChecksum['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_HOST_APP_CHECKSUM, $clientApplicationChecksum['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($clientApplicationChecksum['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_HOST_APP_CHECKSUM, $clientApplicationChecksum['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_HOST_APP_CHECKSUM, $clientApplicationChecksum, $comparison);
    }

    /**
     * Filter the query on the serial_number_type column
     *
     * Example usage:
     * <code>
     * $query->filterBySerialNumberType('fooValue');   // WHERE serial_number_type = 'fooValue'
     * $query->filterBySerialNumberType('%fooValue%', Criteria::LIKE); // WHERE serial_number_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $serialNumberType The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterBySerialNumberType($serialNumberType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($serialNumberType)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_SERIAL_NUMBER_TYPE, $serialNumberType, $comparison);
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
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByAuthorizationLimit($authorizationLimit = null, $comparison = null)
    {
        if (is_array($authorizationLimit)) {
            $useMinMax = false;
            if (isset($authorizationLimit['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorizationLimit['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_AUTHORIZATION_LIMIT, $authorizationLimit, $comparison);
    }

    /**
     * Filter the query on the expires_days column
     *
     * Example usage:
     * <code>
     * $query->filterByExpiresDays(1234); // WHERE expires_days = 1234
     * $query->filterByExpiresDays(array(12, 34)); // WHERE expires_days IN (12, 34)
     * $query->filterByExpiresDays(array('min' => 12)); // WHERE expires_days > 12
     * </code>
     *
     * @param     mixed $expiresDays The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByExpiresDays($expiresDays = null, $comparison = null)
    {
        if (is_array($expiresDays)) {
            $useMinMax = false;
            if (isset($expiresDays['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DAYS, $expiresDays['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expiresDays['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DAYS, $expiresDays['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DAYS, $expiresDays, $comparison);
    }

    /**
     * Filter the query on the expires_date column
     *
     * Example usage:
     * <code>
     * $query->filterByExpiresDate('2011-03-14'); // WHERE expires_date = '2011-03-14'
     * $query->filterByExpiresDate('now'); // WHERE expires_date = '2011-03-14'
     * $query->filterByExpiresDate(array('max' => 'yesterday')); // WHERE expires_date > '2011-03-13'
     * </code>
     *
     * @param     mixed $expiresDate The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function filterByExpiresDate($expiresDate = null, $comparison = null)
    {
        if (is_array($expiresDate)) {
            $useMinMax = false;
            if (isset($expiresDate['min'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DATE, $expiresDate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expiresDate['max'])) {
                $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DATE, $expiresDate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LicenseTypeTableMap::COL_EXPIRES_DATE, $expiresDate, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildLicenseType $licenseType Object to remove from the list of results
     *
     * @return $this|ChildLicenseTypeQuery The current query, for fluid interface
     */
    public function prune($licenseType = null)
    {
        if ($licenseType) {
            $this->addUsingAlias(LicenseTypeTableMap::COL_LICENSE_TYPE_ID, $licenseType->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the product_serial_number_license_types table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTypeTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            LicenseTypeTableMap::clearInstancePool();
            LicenseTypeTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTypeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(LicenseTypeTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            LicenseTypeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            LicenseTypeTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // LicenseTypeQuery
