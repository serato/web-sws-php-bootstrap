<?php

namespace Serato\SwsApp\Test\Propel\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Serato\SwsApp\Test\Propel\Model\LicenseQuery as ChildLicenseQuery;
use Serato\SwsApp\Test\Propel\Model\Map\LicenseTableMap;

/**
 * Base class that represents a row from the 'product_licenses' table.
 *
 *
 *
 * @package    propel.generator..Base
 */
abstract class License implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\LicenseTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the license_serial_number field.
     *
     * Note: this column has a database default value of: ''
     * @var        string
     */
    protected $license_serial_number;

    /**
     * The value for the serial_number field.
     *
     * Note: this column has a database default value of: ''
     * @var        string
     */
    protected $serial_number;

    /**
     * The value for the license_type_id field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $license_type_id;

    /**
     * The value for the date_generated field.
     *
     * Note: this column has a database default value of: '-0001-11-30 00:00:00.000000'
     * @var        DateTime
     */
    protected $date_generated;

    /**
     * The value for the authorization_limit field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $authorization_limit;

    /**
     * The value for the blacklisted field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $blacklisted;

    /**
     * The value for the deleted_flag field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $deleted_flag;

    /**
     * The value for the expires field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $expires;

    /**
     * The value for the user_id field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $user_id;

    /**
     * The value for the user_date_added field.
     *
     * Note: this column has a database default value of: '-0001-11-30 00:00:00.000000'
     * @var        DateTime
     */
    protected $user_date_added;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->license_serial_number = '';
        $this->serial_number = '';
        $this->license_type_id = 0;
        $this->date_generated = PropelDateTime::newInstance('-0001-11-30 00:00:00.000000', null, 'DateTime');
        $this->authorization_limit = 0;
        $this->blacklisted = 0;
        $this->deleted_flag = 0;
        $this->expires = 0;
        $this->user_id = 0;
        $this->user_date_added = PropelDateTime::newInstance('-0001-11-30 00:00:00.000000', null, 'DateTime');
    }

    /**
     * Initializes internal state of Serato\SwsApp\Test\Propel\Model\Base\License object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>License</code> instance.  If
     * <code>obj</code> is an instance of <code>License</code>, delegates to
     * <code>equals(License)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|License The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [license_serial_number] column value.
     *
     * @return string
     */
    public function getId()
    {
        return $this->license_serial_number;
    }

    /**
     * Get the [serial_number] column value.
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->serial_number;
    }

    /**
     * Get the [license_type_id] column value.
     *
     * @return int
     */
    public function getLicenseTypeId()
    {
        return $this->license_type_id;
    }

    /**
     * Get the [optionally formatted] temporal [date_generated] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getDateGenerated($format = NULL)
    {
        if ($format === null) {
            return $this->date_generated;
        } else {
            return $this->date_generated instanceof \DateTimeInterface ? $this->date_generated->format($format) : null;
        }
    }

    /**
     * Get the [authorization_limit] column value.
     *
     * @return int
     */
    public function getAuthorizationLimit()
    {
        return $this->authorization_limit;
    }

    /**
     * Get the [blacklisted] column value.
     *
     * @return int
     */
    public function getBlacklisted()
    {
        return $this->blacklisted;
    }

    /**
     * Get the [deleted_flag] column value.
     *
     * @return int
     */
    public function getDeletedFlag()
    {
        return $this->deleted_flag;
    }

    /**
     * Get the [expires] column value.
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Get the [user_id] column value.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get the [optionally formatted] temporal [user_date_added] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUserAddedAt($format = NULL)
    {
        if ($format === null) {
            return $this->user_date_added;
        } else {
            return $this->user_date_added instanceof \DateTimeInterface ? $this->user_date_added->format($format) : null;
        }
    }

    /**
     * Set the value of [license_serial_number] column.
     *
     * @param string $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->license_serial_number !== $v) {
            $this->license_serial_number = $v;
            $this->modifiedColumns[LicenseTableMap::COL_LICENSE_SERIAL_NUMBER] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [serial_number] column.
     *
     * @param string $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setProductId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->serial_number !== $v) {
            $this->serial_number = $v;
            $this->modifiedColumns[LicenseTableMap::COL_SERIAL_NUMBER] = true;
        }

        return $this;
    } // setProductId()

    /**
     * Set the value of [license_type_id] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setLicenseTypeId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->license_type_id !== $v) {
            $this->license_type_id = $v;
            $this->modifiedColumns[LicenseTableMap::COL_LICENSE_TYPE_ID] = true;
        }

        return $this;
    } // setLicenseTypeId()

    /**
     * Sets the value of [date_generated] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setDateGenerated($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_generated !== null || $dt !== null) {
            if ( ($dt != $this->date_generated) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s.u') === '-0001-11-30 00:00:00.000000') // or the entered value matches the default
                 ) {
                $this->date_generated = $dt === null ? null : clone $dt;
                $this->modifiedColumns[LicenseTableMap::COL_DATE_GENERATED] = true;
            }
        } // if either are not null

        return $this;
    } // setDateGenerated()

    /**
     * Set the value of [authorization_limit] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setAuthorizationLimit($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->authorization_limit !== $v) {
            $this->authorization_limit = $v;
            $this->modifiedColumns[LicenseTableMap::COL_AUTHORIZATION_LIMIT] = true;
        }

        return $this;
    } // setAuthorizationLimit()

    /**
     * Set the value of [blacklisted] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setBlacklisted($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->blacklisted !== $v) {
            $this->blacklisted = $v;
            $this->modifiedColumns[LicenseTableMap::COL_BLACKLISTED] = true;
        }

        return $this;
    } // setBlacklisted()

    /**
     * Set the value of [deleted_flag] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setDeletedFlag($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->deleted_flag !== $v) {
            $this->deleted_flag = $v;
            $this->modifiedColumns[LicenseTableMap::COL_DELETED_FLAG] = true;
        }

        return $this;
    } // setDeletedFlag()

    /**
     * Set the value of [expires] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setExpires($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->expires !== $v) {
            $this->expires = $v;
            $this->modifiedColumns[LicenseTableMap::COL_EXPIRES] = true;
        }

        return $this;
    } // setExpires()

    /**
     * Set the value of [user_id] column.
     *
     * @param int $v new value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setUserId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->user_id !== $v) {
            $this->user_id = $v;
            $this->modifiedColumns[LicenseTableMap::COL_USER_ID] = true;
        }

        return $this;
    } // setUserId()

    /**
     * Sets the value of [user_date_added] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object (for fluent API support)
     */
    public function setUserAddedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->user_date_added !== null || $dt !== null) {
            if ( ($dt != $this->user_date_added) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s.u') === '-0001-11-30 00:00:00.000000') // or the entered value matches the default
                 ) {
                $this->user_date_added = $dt === null ? null : clone $dt;
                $this->modifiedColumns[LicenseTableMap::COL_USER_DATE_ADDED] = true;
            }
        } // if either are not null

        return $this;
    } // setUserAddedAt()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->license_serial_number !== '') {
                return false;
            }

            if ($this->serial_number !== '') {
                return false;
            }

            if ($this->license_type_id !== 0) {
                return false;
            }

            if ($this->date_generated && $this->date_generated->format('Y-m-d H:i:s.u') !== '-0001-11-30 00:00:00.000000') {
                return false;
            }

            if ($this->authorization_limit !== 0) {
                return false;
            }

            if ($this->blacklisted !== 0) {
                return false;
            }

            if ($this->deleted_flag !== 0) {
                return false;
            }

            if ($this->expires !== 0) {
                return false;
            }

            if ($this->user_id !== 0) {
                return false;
            }

            if ($this->user_date_added && $this->user_date_added->format('Y-m-d H:i:s.u') !== '-0001-11-30 00:00:00.000000') {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : LicenseTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->license_serial_number = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : LicenseTableMap::translateFieldName('ProductId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->serial_number = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : LicenseTableMap::translateFieldName('LicenseTypeId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->license_type_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : LicenseTableMap::translateFieldName('DateGenerated', TableMap::TYPE_PHPNAME, $indexType)];
            $this->date_generated = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : LicenseTableMap::translateFieldName('AuthorizationLimit', TableMap::TYPE_PHPNAME, $indexType)];
            $this->authorization_limit = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : LicenseTableMap::translateFieldName('Blacklisted', TableMap::TYPE_PHPNAME, $indexType)];
            $this->blacklisted = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : LicenseTableMap::translateFieldName('DeletedFlag', TableMap::TYPE_PHPNAME, $indexType)];
            $this->deleted_flag = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : LicenseTableMap::translateFieldName('Expires', TableMap::TYPE_PHPNAME, $indexType)];
            $this->expires = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : LicenseTableMap::translateFieldName('UserId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->user_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : LicenseTableMap::translateFieldName('UserAddedAt', TableMap::TYPE_PHPNAME, $indexType)];
            $this->user_date_added = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 10; // 10 = LicenseTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Serato\\SwsApp\\Test\\Propel\\Model\\License'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(LicenseTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildLicenseQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see License::setDeleted()
     * @see License::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildLicenseQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(LicenseTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior

                if (!$this->isColumnModified(LicenseTableMap::COL_DATE_GENERATED)) {
                    $this->setDateGenerated(\Propel\Runtime\Util\PropelDateTime::createHighPrecision());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                LicenseTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER)) {
            $modifiedColumns[':p' . $index++]  = 'license_serial_number';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_SERIAL_NUMBER)) {
            $modifiedColumns[':p' . $index++]  = 'serial_number';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_LICENSE_TYPE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'license_type_id';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_DATE_GENERATED)) {
            $modifiedColumns[':p' . $index++]  = 'date_generated';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_AUTHORIZATION_LIMIT)) {
            $modifiedColumns[':p' . $index++]  = 'authorization_limit';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_BLACKLISTED)) {
            $modifiedColumns[':p' . $index++]  = 'blacklisted';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_DELETED_FLAG)) {
            $modifiedColumns[':p' . $index++]  = 'deleted_flag';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_EXPIRES)) {
            $modifiedColumns[':p' . $index++]  = 'expires';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_USER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'user_id';
        }
        if ($this->isColumnModified(LicenseTableMap::COL_USER_DATE_ADDED)) {
            $modifiedColumns[':p' . $index++]  = 'user_date_added';
        }

        $sql = sprintf(
            'INSERT INTO product_licenses (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'license_serial_number':
                        $stmt->bindValue($identifier, $this->license_serial_number, PDO::PARAM_STR);
                        break;
                    case 'serial_number':
                        $stmt->bindValue($identifier, $this->serial_number, PDO::PARAM_STR);
                        break;
                    case 'license_type_id':
                        $stmt->bindValue($identifier, $this->license_type_id, PDO::PARAM_INT);
                        break;
                    case 'date_generated':
                        $stmt->bindValue($identifier, $this->date_generated ? $this->date_generated->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                    case 'authorization_limit':
                        $stmt->bindValue($identifier, $this->authorization_limit, PDO::PARAM_INT);
                        break;
                    case 'blacklisted':
                        $stmt->bindValue($identifier, $this->blacklisted, PDO::PARAM_INT);
                        break;
                    case 'deleted_flag':
                        $stmt->bindValue($identifier, $this->deleted_flag, PDO::PARAM_INT);
                        break;
                    case 'expires':
                        $stmt->bindValue($identifier, $this->expires, PDO::PARAM_INT);
                        break;
                    case 'user_id':
                        $stmt->bindValue($identifier, $this->user_id, PDO::PARAM_INT);
                        break;
                    case 'user_date_added':
                        $stmt->bindValue($identifier, $this->user_date_added ? $this->user_date_added->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = LicenseTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getProductId();
                break;
            case 2:
                return $this->getLicenseTypeId();
                break;
            case 3:
                return $this->getDateGenerated();
                break;
            case 4:
                return $this->getAuthorizationLimit();
                break;
            case 5:
                return $this->getBlacklisted();
                break;
            case 6:
                return $this->getDeletedFlag();
                break;
            case 7:
                return $this->getExpires();
                break;
            case 8:
                return $this->getUserId();
                break;
            case 9:
                return $this->getUserAddedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array())
    {

        if (isset($alreadyDumpedObjects['License'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['License'][$this->hashCode()] = true;
        $keys = LicenseTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getProductId(),
            $keys[2] => $this->getLicenseTypeId(),
            $keys[3] => $this->getDateGenerated(),
            $keys[4] => $this->getAuthorizationLimit(),
            $keys[5] => $this->getBlacklisted(),
            $keys[6] => $this->getDeletedFlag(),
            $keys[7] => $this->getExpires(),
            $keys[8] => $this->getUserId(),
            $keys[9] => $this->getUserAddedAt(),
        );
        if ($result[$keys[3]] instanceof \DateTime) {
            $result[$keys[3]] = $result[$keys[3]]->format('c');
        }

        if ($result[$keys[9]] instanceof \DateTime) {
            $result[$keys[9]] = $result[$keys[9]]->format('c');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }


        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = LicenseTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setProductId($value);
                break;
            case 2:
                $this->setLicenseTypeId($value);
                break;
            case 3:
                $this->setDateGenerated($value);
                break;
            case 4:
                $this->setAuthorizationLimit($value);
                break;
            case 5:
                $this->setBlacklisted($value);
                break;
            case 6:
                $this->setDeletedFlag($value);
                break;
            case 7:
                $this->setExpires($value);
                break;
            case 8:
                $this->setUserId($value);
                break;
            case 9:
                $this->setUserAddedAt($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = LicenseTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setProductId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setLicenseTypeId($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setDateGenerated($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setAuthorizationLimit($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setBlacklisted($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setDeletedFlag($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setExpires($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setUserId($arr[$keys[8]]);
        }
        if (array_key_exists($keys[9], $arr)) {
            $this->setUserAddedAt($arr[$keys[9]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\Serato\SwsApp\Test\Propel\Model\License The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(LicenseTableMap::DATABASE_NAME);

        if ($this->isColumnModified(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER)) {
            $criteria->add(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $this->license_serial_number);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_SERIAL_NUMBER)) {
            $criteria->add(LicenseTableMap::COL_SERIAL_NUMBER, $this->serial_number);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_LICENSE_TYPE_ID)) {
            $criteria->add(LicenseTableMap::COL_LICENSE_TYPE_ID, $this->license_type_id);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_DATE_GENERATED)) {
            $criteria->add(LicenseTableMap::COL_DATE_GENERATED, $this->date_generated);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_AUTHORIZATION_LIMIT)) {
            $criteria->add(LicenseTableMap::COL_AUTHORIZATION_LIMIT, $this->authorization_limit);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_BLACKLISTED)) {
            $criteria->add(LicenseTableMap::COL_BLACKLISTED, $this->blacklisted);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_DELETED_FLAG)) {
            $criteria->add(LicenseTableMap::COL_DELETED_FLAG, $this->deleted_flag);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_EXPIRES)) {
            $criteria->add(LicenseTableMap::COL_EXPIRES, $this->expires);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_USER_ID)) {
            $criteria->add(LicenseTableMap::COL_USER_ID, $this->user_id);
        }
        if ($this->isColumnModified(LicenseTableMap::COL_USER_DATE_ADDED)) {
            $criteria->add(LicenseTableMap::COL_USER_DATE_ADDED, $this->user_date_added);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildLicenseQuery::create();
        $criteria->add(LicenseTableMap::COL_LICENSE_SERIAL_NUMBER, $this->license_serial_number);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (license_serial_number column).
     *
     * @param       string $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Serato\SwsApp\Test\Propel\Model\License (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setId($this->getId());
        $copyObj->setProductId($this->getProductId());
        $copyObj->setLicenseTypeId($this->getLicenseTypeId());
        $copyObj->setDateGenerated($this->getDateGenerated());
        $copyObj->setAuthorizationLimit($this->getAuthorizationLimit());
        $copyObj->setBlacklisted($this->getBlacklisted());
        $copyObj->setDeletedFlag($this->getDeletedFlag());
        $copyObj->setExpires($this->getExpires());
        $copyObj->setUserId($this->getUserId());
        $copyObj->setUserAddedAt($this->getUserAddedAt());
        if ($makeNew) {
            $copyObj->setNew(true);
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Serato\SwsApp\Test\Propel\Model\License Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->license_serial_number = null;
        $this->serial_number = null;
        $this->license_type_id = null;
        $this->date_generated = null;
        $this->authorization_limit = null;
        $this->blacklisted = null;
        $this->deleted_flag = null;
        $this->expires = null;
        $this->user_id = null;
        $this->user_date_added = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
        } // if ($deep)

    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(LicenseTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
