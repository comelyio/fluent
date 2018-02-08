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

use Comely\Fluent\Database\Table\Columns\AbstractColumn;
use Comely\Fluent\Database\Table\Columns\DoubleColumn;
use Comely\Fluent\Database\Table\Columns\FloatColumn;
use Comely\Fluent\Exception\FluentException;
use Comely\Fluent\Exception\FluentModelException;
use Comely\Fluent\Exception\FluentTableException;
use Comely\Fluent\Fluent;
use Comely\Kernel\Comely;

/**
 * Class Model
 * @package Comely\Fluent\ORM
 */
abstract class Model
{
    public const TABLE = null;

    /** @var string */
    protected $name;
    /** @var \Comely\Fluent\Database\Table */
    protected $table;
    /** @var array */
    protected $privateProps;

    /** @var array */
    private $original;

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


}