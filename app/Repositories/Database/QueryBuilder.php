<?php

declare(strict_types=1);

namespace App\Repositories\Database;

use PDO;

class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $selects = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private string $type = 'select';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'AND',
        ];
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'OR',
        ];
        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => "({$placeholders})",
            'logic' => 'AND',
        ];
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function insert(array $data): int
    {
        $this->type = 'insert';
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $bindings = array_values($data);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        
        $this->reset();

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        $this->type = 'update';
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }
        $bindings = array_merge(array_values($data), $this->bindings);

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        $sql .= $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        
        $this->reset();

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $this->type = 'delete';
        $bindings = $this->bindings;
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        
        $this->reset();

        return $stmt->rowCount();
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $bindings = $this->bindings;
        $this->reset();
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $limit = $this->limit;
        $this->limit(1);
        $results = $this->get();
        if ($limit !== null) {
            $this->limit = $limit;
        }
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $bindings = $this->bindings;
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->reset();

        return (int) ($result['count'] ?? 0);
    }

    private function buildSelectQuery(): string
    {
        $select = implode(', ', $this->selects);
        $sql = "SELECT {$select} FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        if ($this->orderBy !== null) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $clauses = [];
        foreach ($this->wheres as $index => $where) {
            $logic = $index > 0 ? " {$where['logic']} " : '';
            $column = $where['column'];
            $operator = $where['operator'];

            if ($operator === 'IN') {
                $clauses[] = "{$logic}{$column} {$operator} {$where['value']}";
            } else {
                $clauses[] = "{$logic}{$column} {$operator} ?";
            }
        }

        return ' WHERE ' . implode('', $clauses);
    }

    private function reset(): void
    {
        $this->selects = ['*'];
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = null;
        $this->limit = null;
        $this->offset = null;
        $this->type = 'select';
    }
}
