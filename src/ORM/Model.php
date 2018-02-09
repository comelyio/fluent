<?php
/**
 * This file is part of Comely Fluent ORM package.
 * https://github.com/comelyio/fluent
 *
 * Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/fluent/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Fluent\ORM;

use Comely\Fluent\Database\Table;
use Comely\Fluent\Database\Table\Columns\AbstractColumn;
use Comely\Fluent\Database\Table\Columns\DoubleColumn;
use Comely\Fluent\Database\Table\Columns\FloatColumn;
use Comely\Fluent\Exception\FluentException;
use Comely\Fluent\Exception\FluentModelException;
use Comely\Fluent\Exception\FluentTableException;
use Comely\Fluent\Fluent;
use Comely\Fluent\ORM\Model\Query;
use Comely\Kernel\Comely;

/**
 * Class Model
 * @package Comely\Fluent\ORM
 */
abstract class Model
{
    public const TABLE = null;

    /** @var string */
    private $name;
    /** @var \Comely\Fluent\Database\Table */
    private $table;
    /** @var array */
    private $privateProps;

    /** @var array */
    private $original;
    /** @var null|AbstractColumn */
    private $primaryColumn;

    /**
     * Model constructor.
     * @param array $row
     * @throws FluentModelException
     */
    final public function __construct(array $row = null)
    {
        $this->name = get_called_class();
        $this->privateProps = [];
        $this->original = [];

        // Check TABLE constant
        $table = @constant('static::TABLE');
        if (!is_string($table) || !preg_match('/^[a-zA-Z0-9\_\\\]+$/', $table)) {
            throw new FluentModelException(
                sprintf('Invalid value for TABLE constant in FluentModel class "%s"', $this->name)
            );
        }

        // Load table
        try {
            $this->table = Fluent::Retrieve($table);
        } catch (FluentException $e) {
            throw new FluentModelException($e->getMessage());
        }

        // Populate
        if (is_array($row)) {
            $this->populate($row);
        }
    }

    /**
     * @param array $row
     * @throws FluentModelException
     */
    final private function populate(array $row): void
    {
        // Make sure we have all the keys present
        $columnsKeys = $this->table->columns()->names();
        foreach ($columnsKeys as $columnsKey) {
            if (!array_key_exists($columnsKey, $row)) {
                throw new FluentModelException(sprintf('Missing column "%s" in input row', $columnsKey));
            }
        }

        foreach ($row as $key => $value) {
            // Row might have more props. defined as compared to column,
            // Ignore undefined columns
            try {
                /** @var AbstractColumn $column */
                $column = $this->table->columns()->get($key);
            } catch (FluentTableException $e) {
            }

            // Column exists?
            if (isset($column)) {
                // Casting
                switch ($column->_scalar) {
                    case "integer":
                        $value = intval($value);
                        break;
                    case "double": // float or double
                        /** @var FloatColumn|DoubleColumn $column */
                        $value = round($value, ($column->_scale + 1));
                        break;

                }

                // Set property
                $this->set(Comely::camelCase($key), $value);
                // Preserve original
                $this->original[$key] = $value;
            }
        }
    }

    /**
     * @return string
     */
    final public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Table
     */
    final public function table(): Table
    {
        return $this->table;
    }

    /**
     * @param string $prop
     * @param $value
     */
    final public function set(string $prop, $value): void
    {
        // Check if property exists with get_called_class() instead of $this instance for only public props.
        // $this->name = get_called_class()
        if (property_exists($this->name, $prop)) {
            $this->$prop = $value; // Public property
        } else {
            $this->privateProps[$prop] = $value; // Private
        }
    }

    /**
     * @param string $prop
     * @return mixed|null
     */
    final public function private(string $prop)
    {
        return $this->privateProps[$prop] ?? null;
    }

    /**
     * @param AbstractColumn $column
     * @param string $key
     * @param $value
     * @throws FluentModelException
     */
    final public function validateColumnValue(AbstractColumn $column, string $key, $value): void
    {
        if (is_null($value)) {
            if (!isset($column->_attrs["nullable"])) {
                throw FluentModelException::BadValue($this->name, $key, 'cannot be NULL');
            }
        } else {
            // Compare scalar type
            $valueType = gettype($value);
            if ($valueType !== $column->_scalar) {
                throw FluentModelException::BadValue(
                    $this->name,
                    $key,
                    sprintf('must be of type "%s", given "%s"', $column->_scalar, $valueType)
                );
            }
        }
    }

    /**
     * @return array
     */
    final public function original(): array
    {
        return $this->original;
    }

    /**
     * @return array
     * @throws FluentModelException
     */
    final public function difference(): array
    {
        $difference = [];
        foreach ($this->table->columns() as $column) {
            $camelKey = Comely::camelCase($column->_name);
            $existingValue = $this->$camelKey ?? $this->privateProps[$camelKey] ?? null;
            $originalValue = $this->original[$column->_name] ?? null;

            // Validate existing value as per column type
            $this->validateColumnValue($column, $camelKey, $existingValue);

            // Compare with original value
            if (is_null($originalValue)) {
                // Original value does NOT exist (or is NULL)
                $difference[$column->_name] = $existingValue;
            } else {
                // Original value found, compare
                if ($existingValue !== $originalValue) {
                    $difference[$column->_name] = $existingValue;
                }
            }
        }

        return $difference;
    }

    /**
     * @return AbstractColumn|null
     */
    final public function getPrimaryColumn(): ?AbstractColumn
    {
        if ($this->primaryColumn) {
            return $this->primaryColumn;
        }

        // grab Columns
        $columns = $this->table->columns();

        // Table has PRIMARY KEY defined?
        if ($columns->_primary) {
            try {
                $this->primaryColumn = $columns->get($columns->_primary);
                return $this->primaryColumn;
            } catch (FluentTableException $e) {
            }
        }

        // Look for first UNIQUE KEY
        foreach ($this->table->columns() as $column) {
            if (isset($column->_attrs["unique"])) {
                $this->primaryColumn = $column;
                return $this->primaryColumn;
            }
        }

        return null;
    }

    /**
     * @return Query
     */
    final public function query(): Query
    {
        return new Query($this);
    }
}