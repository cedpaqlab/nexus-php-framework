<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Models\Order;
use App\Models\OrderQuery;
use App\Repositories\Connectors\PropelInitializer;
use Propel\Runtime\Propel;
use Propel\Runtime\Exception\PropelException;

class OrderConnector
{
    public function __construct()
    {
        PropelInitializer::initialize();
    }

    public function findOrderById(int $id): ?Order
    {
        return $this->executeQuery(fn() => OrderQuery::create()->findPk($id));
    }

    public function findOrdersByUserId(int $userId): array
    {
        return $this->executeQuery(
            fn() => OrderQuery::create()
                ->filterByUserId($userId)
                ->orderByCreatedAt('DESC')
                ->find()
                ->getData(),
            []
        );
    }

    public function findAllOrders(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->executeQuery(
            fn() => $this->buildQuery($conditions, $orderBy, $limit, $offset)->find()->getData(),
            []
        );
    }

    public function createOrder(array $data): Order
    {
        $order = new Order();
        $order->setUserId($data['user_id']);
        $order->setProductId($data['product_id']);
        $order->setQuantity($data['quantity']);
        $order->setTotalPrice($data['total_price']);
        $order->setStatus($data['status'] ?? 'pending');
        $order->save();

        return $order;
    }

    public function updateOrder(Order $order, array $data): Order
    {
        if (isset($data['quantity'])) {
            $order->setQuantity($data['quantity']);
        }
        if (isset($data['total_price'])) {
            $order->setTotalPrice($data['total_price']);
        }
        if (isset($data['status'])) {
            $order->setStatus($data['status']);
        }
        $order->save();

        return $order;
    }

    public function deleteOrder(Order $order): void
    {
        $order->delete();
    }

    public function countOrders(array $conditions = []): int
    {
        return $this->executeQuery(
            fn() => $this->buildQuery($conditions, [], null, null)->count(),
            0
        );
    }

    public function executeInTransaction(callable $callback): mixed
    {
        $connection = Propel::getConnection();
        $connection->beginTransaction();

        try {
            $result = $callback();
            $connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function buildQuery(array $conditions, array $orderBy, ?int $limit, ?int $offset): OrderQuery
    {
        $query = OrderQuery::create();

        foreach ($conditions as $column => $value) {
            $method = 'filterBy' . $this->camelize($column);
            if (method_exists($query, $method)) {
                $query->$method($value);
            }
        }

        foreach ($orderBy as $column => $direction) {
            $method = 'orderBy' . $this->camelize($column);
            if (method_exists($query, $method)) {
                $query->$method($direction);
            }
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query;
    }

    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    private function executeQuery(callable $query, mixed $default = null): mixed
    {
        try {
            return $query();
        } catch (PropelException $e) {
            return $default;
        }
    }
}
