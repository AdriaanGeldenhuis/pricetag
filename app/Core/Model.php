<?php
/**
 * Base Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = ['password'];
    protected static bool $timestamps = true;

    protected array $attributes = [];
    protected array $original = [];
    public bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get database connection
     */
    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Set original data (used when loading from database)
     */
    public function setOriginal(array $data): self
    {
        $this->original = $data;
        // Also set all attributes from original data for proper model hydration
        $this->attributes = array_merge($data, $this->attributes);
        return $this;
    }

    /**
     * Get attribute
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set attribute
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Check if attribute exists
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Find by primary key
     */
    public static function find(int $id): ?static
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $model = new static();
        $model->setOriginal($data);
        $model->exists = true;
        return $model;
    }

    /**
     * Find or fail
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);

        if (!$model) {
            throw new \RuntimeException(static::class . " with ID $id not found");
        }

        return $model;
    }

    /**
     * Get all records
     */
    public static function all(): array
    {
        $stmt = static::db()->query("SELECT * FROM " . static::$table);
        $results = [];

        while ($data = $stmt->fetch()) {
            $model = new static();
            $model->setOriginal($data);
            $model->exists = true;
            $results[] = $model;
        }

        return $results;
    }

    /**
     * Create new record
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Where clause builder
     */
    public static function where(string $column, $operator, $value = null): QueryBuilder
    {
        return (new QueryBuilder(static::class))->where($column, $operator, $value);
    }

    /**
     * Order by clause
     */
    public static function orderBy(string $column, string $direction = 'ASC'): QueryBuilder
    {
        return (new QueryBuilder(static::class))->orderBy($column, $direction);
    }

    /**
     * Limit clause
     */
    public static function limit(int $limit): QueryBuilder
    {
        return (new QueryBuilder(static::class))->limit($limit);
    }

    /**
     * Save model
     */
    public function save(): bool
    {
        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            return $this->update();
        }

        return $this->insert();
    }

    /**
     * Insert new record
     */
    protected function insert(): bool
    {
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $result = $stmt->execute(array_values($this->attributes));

        if ($result) {
            $this->attributes[static::$primaryKey] = (int) static::db()->lastInsertId();
            $this->exists = true;
            $this->original = $this->attributes;
        }

        return $result;
    }

    /**
     * Update existing record
     */
    protected function update(): bool
    {
        $changed = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== static::$primaryKey && ($this->original[$key] ?? null) !== $value) {
                $changed[$key] = $value;
            }
        }

        if (empty($changed)) {
            return true;
        }

        $sets = array_map(fn($col) => "$col = ?", array_keys($changed));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            static::$table,
            implode(', ', $sets),
            static::$primaryKey
        );

        $values = array_values($changed);
        $values[] = $this->attributes[static::$primaryKey];

        $stmt = static::db()->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            $this->original = $this->attributes;
        }

        return $result;
    }

    /**
     * Delete record
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ?",
            static::$table,
            static::$primaryKey
        );

        $stmt = static::db()->prepare($sql);
        return $stmt->execute([$this->attributes[static::$primaryKey]]);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $data = $this->attributes;

        foreach (static::$hidden as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Get table name
     */
    public static function getTable(): string
    {
        return static::$table;
    }
}

/**
 * Query Builder for fluent queries
 */
class QueryBuilder
{
    private string $modelClass;
    private array $wheres = [];
    private array $bindings = [];
    private array $orders = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private array $selects = ['*'];

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function select(...$columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    public function where(string $column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "$column IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "$column IS NOT NULL";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSql();
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($this->bindings);

        $results = [];
        while ($data = $stmt->fetch()) {
            $model = new $this->modelClass();
            $model->setOriginal($data);
            $model->exists = true;
            $results[] = $model;
        }

        return $results;
    }

    public function first(): ?object
    {
        $this->limitValue = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $sql = $this->buildSql();
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($this->bindings);
        return (int) $stmt->fetchColumn();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $total = $this->count();
        $this->selects = ['*'];

        $this->limitValue = $perPage;
        $this->offsetValue = ($page - 1) * $perPage;

        $data = $this->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    private function buildSql(): string
    {
        $table = $this->modelClass::getTable();
        $select = implode(', ', $this->selects);

        $sql = "SELECT $select FROM $table";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }
}
