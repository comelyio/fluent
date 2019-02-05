<?php
/**
 * This file is part of Comely Fluent ORM package.
 * https://github.com/comelyio/fluent
 *
 * Copyright (c) 2018-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/fluent/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Fluent\Database\Table\Columns;

use Comely\Fluent\Exception\FluentTableException;

/**
 * Class AbstractColumn
 * @package Comely\Fluent\Database\Table\Columns
 * @property string $_name
 * @property array $_attrs
 * @property string $_scalar
 * @property null|string|int $_default
 * @method null|string getColumnSQL (string $driver)
 */
abstract class AbstractColumn implements ColumnInterface
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $type;
    /** @var string */
    protected $scalarType;
    /** @var null|string|int */
    protected $default;
    /** @var array */
    protected $attributes;

    /**
     * AbstractColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->attributes = [];
    }

    /**
     * @param $prop
     * @return mixed
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_name":
                return $this->name;
            case "_attrs":
                return $this->attributes;
            case "_scalar":
                return $this->scalarType;
            case "_default":
                return $this->default;
        }

        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @throws FluentTableException
     */
    final public function __set($prop, $value)
    {
        throw new FluentTableException('Overriding inaccessible properties not allowed');
    }

    /**
     * @param $name
     * @param $arguments
     * @return null|string
     * @throws FluentTableException
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case "getColumnSQL":
                return $this->columnSQL(strval($arguments[0] ?? ""));
        }

        throw new FluentTableException(sprintf('Calling inaccessible method on column "%s"', $this->name));
    }

    /**
     * @param $value
     * @return AbstractColumn
     * @throws FluentTableException
     */
    protected function setDefaultValue($value): self
    {
        if (is_null($value)) {
            $nullability = $this->attributes["nullable"] ?? false;
            if (!$nullability) {
                throw FluentTableException::ColumnError(
                    $this->name,
                    'Default cannot be NULL, column is not nullable'
                );
            }
        }

        $this->default = $value;
        return $this;
    }

    /**
     * @param string $driver
     * @return null|string
     */
    abstract protected function columnSQL(string $driver): ?string;
}