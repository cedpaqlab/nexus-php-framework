<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Models\Product;
use App\Models\ProductQuery;
use App\Repositories\Connectors\PropelInitializer;
use Propel\Runtime\Exception\PropelException;

class ProductConnector
{
    public function __construct()
    {
        PropelInitializer::initialize();
    }

    public function findProductById(int $id): ?Product
    {
        return $this->executeQuery(fn() => ProductQuery::create()->findPk($id));
    }

    public function findAllProducts(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->executeQuery(
            fn() => $this->buildQuery($conditions, $orderBy, $limit, $offset)->find()->getData(),
            []
        );
    }

    public function createProduct(array $data): Product
    {
        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? '');
        $product->setPrice($data['price']);
        $product->setStock($data['stock'] ?? 0);
        $product->setStatus($data['status'] ?? 'active');
        $product->save();

        return $product;
    }

    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['stock'])) {
            $product->setStock($data['stock']);
        }
        if (isset($data['status'])) {
            $product->setStatus($data['status']);
        }
        $product->save();

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    public function countProducts(array $conditions = []): int
    {
        return $this->executeQuery(
            fn() => $this->buildQuery($conditions, [], null, null)->count(),
            0
        );
    }

    private function buildQuery(array $conditions, array $orderBy, ?int $limit, ?int $offset): ProductQuery
    {
        $query = ProductQuery::create();

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
