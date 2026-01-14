<?php

namespace App\Models\Map;

use App\Models\Order;
use App\Models\OrderQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'orders' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class OrderTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Models.Map.OrderTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'orders';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'Order';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\App\\Models\\Order';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Models.Order';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 8;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 8;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'orders.id';

    /**
     * the column name for the user_id field
     */
    public const COL_USER_ID = 'orders.user_id';

    /**
     * the column name for the product_id field
     */
    public const COL_PRODUCT_ID = 'orders.product_id';

    /**
     * the column name for the quantity field
     */
    public const COL_QUANTITY = 'orders.quantity';

    /**
     * the column name for the total_price field
     */
    public const COL_TOTAL_PRICE = 'orders.total_price';

    /**
     * the column name for the status field
     */
    public const COL_STATUS = 'orders.status';

    /**
     * the column name for the created_at field
     */
    public const COL_CREATED_AT = 'orders.created_at';

    /**
     * the column name for the updated_at field
     */
    public const COL_UPDATED_AT = 'orders.updated_at';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /** The enumerated values for the status field */
    public const COL_STATUS_PENDING = 'pending';
    public const COL_STATUS_COMPLETED = 'completed';
    public const COL_STATUS_CANCELLED = 'cancelled';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'UserId', 'ProductId', 'Quantity', 'TotalPrice', 'Status', 'CreatedAt', 'UpdatedAt', ],
        self::TYPE_CAMELNAME     => ['id', 'userId', 'productId', 'quantity', 'totalPrice', 'status', 'createdAt', 'updatedAt', ],
        self::TYPE_COLNAME       => [OrderTableMap::COL_ID, OrderTableMap::COL_USER_ID, OrderTableMap::COL_PRODUCT_ID, OrderTableMap::COL_QUANTITY, OrderTableMap::COL_TOTAL_PRICE, OrderTableMap::COL_STATUS, OrderTableMap::COL_CREATED_AT, OrderTableMap::COL_UPDATED_AT, ],
        self::TYPE_FIELDNAME     => ['id', 'user_id', 'product_id', 'quantity', 'total_price', 'status', 'created_at', 'updated_at', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['Id' => 0, 'UserId' => 1, 'ProductId' => 2, 'Quantity' => 3, 'TotalPrice' => 4, 'Status' => 5, 'CreatedAt' => 6, 'UpdatedAt' => 7, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'userId' => 1, 'productId' => 2, 'quantity' => 3, 'totalPrice' => 4, 'status' => 5, 'createdAt' => 6, 'updatedAt' => 7, ],
        self::TYPE_COLNAME       => [OrderTableMap::COL_ID => 0, OrderTableMap::COL_USER_ID => 1, OrderTableMap::COL_PRODUCT_ID => 2, OrderTableMap::COL_QUANTITY => 3, OrderTableMap::COL_TOTAL_PRICE => 4, OrderTableMap::COL_STATUS => 5, OrderTableMap::COL_CREATED_AT => 6, OrderTableMap::COL_UPDATED_AT => 7, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'user_id' => 1, 'product_id' => 2, 'quantity' => 3, 'total_price' => 4, 'status' => 5, 'created_at' => 6, 'updated_at' => 7, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ID',
        'Order.Id' => 'ID',
        'id' => 'ID',
        'order.id' => 'ID',
        'OrderTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'orders.id' => 'ID',
        'UserId' => 'USER_ID',
        'Order.UserId' => 'USER_ID',
        'userId' => 'USER_ID',
        'order.userId' => 'USER_ID',
        'OrderTableMap::COL_USER_ID' => 'USER_ID',
        'COL_USER_ID' => 'USER_ID',
        'user_id' => 'USER_ID',
        'orders.user_id' => 'USER_ID',
        'ProductId' => 'PRODUCT_ID',
        'Order.ProductId' => 'PRODUCT_ID',
        'productId' => 'PRODUCT_ID',
        'order.productId' => 'PRODUCT_ID',
        'OrderTableMap::COL_PRODUCT_ID' => 'PRODUCT_ID',
        'COL_PRODUCT_ID' => 'PRODUCT_ID',
        'product_id' => 'PRODUCT_ID',
        'orders.product_id' => 'PRODUCT_ID',
        'Quantity' => 'QUANTITY',
        'Order.Quantity' => 'QUANTITY',
        'quantity' => 'QUANTITY',
        'order.quantity' => 'QUANTITY',
        'OrderTableMap::COL_QUANTITY' => 'QUANTITY',
        'COL_QUANTITY' => 'QUANTITY',
        'orders.quantity' => 'QUANTITY',
        'TotalPrice' => 'TOTAL_PRICE',
        'Order.TotalPrice' => 'TOTAL_PRICE',
        'totalPrice' => 'TOTAL_PRICE',
        'order.totalPrice' => 'TOTAL_PRICE',
        'OrderTableMap::COL_TOTAL_PRICE' => 'TOTAL_PRICE',
        'COL_TOTAL_PRICE' => 'TOTAL_PRICE',
        'total_price' => 'TOTAL_PRICE',
        'orders.total_price' => 'TOTAL_PRICE',
        'Status' => 'STATUS',
        'Order.Status' => 'STATUS',
        'status' => 'STATUS',
        'order.status' => 'STATUS',
        'OrderTableMap::COL_STATUS' => 'STATUS',
        'COL_STATUS' => 'STATUS',
        'orders.status' => 'STATUS',
        'CreatedAt' => 'CREATED_AT',
        'Order.CreatedAt' => 'CREATED_AT',
        'createdAt' => 'CREATED_AT',
        'order.createdAt' => 'CREATED_AT',
        'OrderTableMap::COL_CREATED_AT' => 'CREATED_AT',
        'COL_CREATED_AT' => 'CREATED_AT',
        'created_at' => 'CREATED_AT',
        'orders.created_at' => 'CREATED_AT',
        'UpdatedAt' => 'UPDATED_AT',
        'Order.UpdatedAt' => 'UPDATED_AT',
        'updatedAt' => 'UPDATED_AT',
        'order.updatedAt' => 'UPDATED_AT',
        'OrderTableMap::COL_UPDATED_AT' => 'UPDATED_AT',
        'COL_UPDATED_AT' => 'UPDATED_AT',
        'updated_at' => 'UPDATED_AT',
        'orders.updated_at' => 'UPDATED_AT',
    ];

    /**
     * The enumerated values for this table
     *
     * @var array<string, array<string>>
     */
    protected static $enumValueSets = [
                OrderTableMap::COL_STATUS => [
                            self::COL_STATUS_PENDING,
            self::COL_STATUS_COMPLETED,
            self::COL_STATUS_CANCELLED,
        ],
    ];

    /**
     * Gets the list of values for all ENUM and SET columns
     * @return array
     */
    public static function getValueSets(): array
    {
      return static::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM or SET column
     * @param string $colname
     * @return array list of possible values for the column
     */
    public static function getValueSet(string $colname): array
    {
        $valueSets = self::getValueSets();

        return $valueSets[$colname];
    }

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('orders');
        $this->setPhpName('Order');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\App\\Models\\Order');
        $this->setPackage('Models');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('user_id', 'UserId', 'INTEGER', 'users', 'id', true, null, null);
        $this->addForeignKey('product_id', 'ProductId', 'INTEGER', 'products', 'id', true, null, null);
        $this->addColumn('quantity', 'Quantity', 'INTEGER', true, null, null);
        $this->addColumn('total_price', 'TotalPrice', 'DECIMAL', true, 10, null);
        $this->addColumn('status', 'Status', 'ENUM', true, null, 'pending');
        $this->getColumn('status')->setValueSet(array (
  0 => 'pending',
  1 => 'completed',
  2 => 'cancelled',
));
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, 'CURRENT_TIMESTAMP');
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, 'CURRENT_TIMESTAMP');
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('User', '\\App\\Models\\User', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':user_id',
    1 => ':id',
  ),
), null, null, null, false);
        $this->addRelation('Product', '\\App\\Models\\Product', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':product_id',
    1 => ':id',
  ),
), null, null, null, false);
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
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
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
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
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? OrderTableMap::CLASS_DEFAULT : OrderTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (Order object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = OrderTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = OrderTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + OrderTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = OrderTableMap::OM_CLASS;
            /** @var Order $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            OrderTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = OrderTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = OrderTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Order $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                OrderTableMap::addInstanceToPool($obj, $key);
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
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(OrderTableMap::COL_ID);
            $criteria->addSelectColumn(OrderTableMap::COL_USER_ID);
            $criteria->addSelectColumn(OrderTableMap::COL_PRODUCT_ID);
            $criteria->addSelectColumn(OrderTableMap::COL_QUANTITY);
            $criteria->addSelectColumn(OrderTableMap::COL_TOTAL_PRICE);
            $criteria->addSelectColumn(OrderTableMap::COL_STATUS);
            $criteria->addSelectColumn(OrderTableMap::COL_CREATED_AT);
            $criteria->addSelectColumn(OrderTableMap::COL_UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.user_id');
            $criteria->addSelectColumn($alias . '.product_id');
            $criteria->addSelectColumn($alias . '.quantity');
            $criteria->addSelectColumn($alias . '.total_price');
            $criteria->addSelectColumn($alias . '.status');
            $criteria->addSelectColumn($alias . '.created_at');
            $criteria->addSelectColumn($alias . '.updated_at');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(OrderTableMap::COL_ID);
            $criteria->removeSelectColumn(OrderTableMap::COL_USER_ID);
            $criteria->removeSelectColumn(OrderTableMap::COL_PRODUCT_ID);
            $criteria->removeSelectColumn(OrderTableMap::COL_QUANTITY);
            $criteria->removeSelectColumn(OrderTableMap::COL_TOTAL_PRICE);
            $criteria->removeSelectColumn(OrderTableMap::COL_STATUS);
            $criteria->removeSelectColumn(OrderTableMap::COL_CREATED_AT);
            $criteria->removeSelectColumn(OrderTableMap::COL_UPDATED_AT);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.user_id');
            $criteria->removeSelectColumn($alias . '.product_id');
            $criteria->removeSelectColumn($alias . '.quantity');
            $criteria->removeSelectColumn($alias . '.total_price');
            $criteria->removeSelectColumn($alias . '.status');
            $criteria->removeSelectColumn($alias . '.created_at');
            $criteria->removeSelectColumn($alias . '.updated_at');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(OrderTableMap::DATABASE_NAME)->getTable(OrderTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a Order or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or Order object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(OrderTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \App\Models\Order) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(OrderTableMap::DATABASE_NAME);
            $criteria->add(OrderTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = OrderQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            OrderTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                OrderTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the orders table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return OrderQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Order or Criteria object.
     *
     * @param mixed $criteria Criteria or Order object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(OrderTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Order object
        }

        if ($criteria->containsKey(OrderTableMap::COL_ID) && $criteria->keyContainsValue(OrderTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.OrderTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = OrderQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
