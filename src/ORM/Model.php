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
use Comely\Fluent\ORM\Model\Lock;
use Comely\Fluent\ORM\Model\Query;
use Comely\Kernel\Comely;

/**
 * Class Model
 * @package Comely\Fluent\ORM
 * @method void onLoad()
 * @method void onSleep()
 * @method void onWakeup()
 * @method void beforeQuery()
 * @method void afterQuery()
 */
abstract class Model implements \Serializable
{
    public const TABLE = null;
    public const SERIALIZABLE = false;

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
        $table = static::TABLE;
        if (!is_string($table) || !preg_match('/^[a-zA-Z0-9\_\\\]+$/', $table)) {
            throw new FluentModelException(
                sprintf('Invalid value for TABLE constant in FluentModel class "%s"', $this->name)
            );
        }

        // Load table
        $this->loadTable($table);

        // Populate
        if (is_array($row)) {
            $this->populate($row);
        }

        // Callback event: onLoad
        if (method_exists($this, "onLoad")) {
            call_user_func([$this, "onLoad"]);
        }
    }

    /**
     * @return bool
     * @throws FluentModelException
     */
    final private function serializable(): bool
    {
        $serializable = static::SERIALIZABLE;
        if (!is_bool($serializable)) {
            throw new FluentModelException(
                sprintf('constant SERIALIZABLE declared in "%s" must be of type boolean', $this->name)
            );
        }

        return $serializable;
    }

    /**
     * @param string $table
     * @throws FluentModelException
     */
    final private function loadTable(string $table): void
    {
        try {
            $this->table = Fluent::Retrieve($table);
        } catch (FluentException $e) {
            throw new FluentModelException($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws FluentModelException
     */
    final public function serialize(): string
    {
        // Serialization allowed for this model?
        if (!$this->serializable()) {
            throw new FluentModelException(
                sprintf('Cannot serialize "%s", constant SERIALIZABLE must be set to bool(TRUE)', $this->name)
            );
        }

        // Callback event: onSleep
        if (method_exists($this, "onSleep")) {
            call_user_func([$this, "onSleep"]);
        }

        // Get all properties from ReflectionClass
        $props = [];
        $reflect = new \ReflectionClass($this);
        /** @var $prop \ReflectionProperty */
        foreach ($reflect->getProperties() as $prop) {
            if ($prop->getDeclaringClass() === __CLASS__) {
                // Private props. from this (abstract Model) class will not be reflected anyway
                // still ignore any visible property declared here = future-proof
                continue;
            }

            $prop->setAccessible(true); // Set accessibility

            if (!$prop->isDefault()) {
                continue; // Ignore dynamically declared property
            } elseif ($prop->isStatic()) {
                continue; // Ignore static properties
            }

            // Append
            $props[$prop->getName()] = $prop->getValue($this);
        }

        // Manually declare properties of this (abstract Model) class
        $fluent = [];
        $fluent["name"] = $this->name;
        $fluent["table"] = $this->table->_name;
        $fluent["privateProps"] = $this->privateProps;
        $fluent["original"] = $this->original;
        $fluent["primaryColumn"] = null;

        return serialize(["props" => $props, "fluent" => $fluent]);
    }

    /**
     * @param string $serialized
     * @throws FluentModelException
     */
    final public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $props = $data["props"] ?? null;
        $fluent = $data["fluent"] ?? null;
        if (!is_array($props) || !is_array($fluent)) {
            throw new FluentModelException(
                sprintf('Cannot retrieve "%s" instance, serialized string may be corrupted', $this->name)
            );
        }

        $reflect = new \ReflectionClass($this);
        /** @var $prop \ReflectionProperty */
        foreach ($reflect->getProperties() as $prop) {
            $propValue = $props[$prop->getName()] ?? null;
            if ($propValue) {
                $prop->setAccessible(true); // Set accessibility
                $prop->setValue($this, $propValue);
            }
        }
        unset($prop, $value);

        // Fluent Properties
        foreach ($fluent as $prop => $value) {
            $this->$prop = $value; // Set fluent props.
        }

        // Bootstrap
        /** @noinspection PhpStrictTypeCheckingInspection */
        $this->loadTable($this->table);

        // Callback event: onWakeup
        if (method_exists($this, "onWakeup")) {
            call_user_func([$this, "onWakeup"]);
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
     * @throws FluentModelException
     */
    final public function set(string $prop, $value): void
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new FluentModelException(sprintf('Cannot set non-scalar value for "%s" prop.', $prop));
        }

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
    final public function get(string $prop)
    {
        return $this->$prop ?? $this->privateProps[$prop] ?? null;
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

    /**
     * @return Lock
     */
    final public function lock(): Lock
    {
        return new Lock($this);
    }
}