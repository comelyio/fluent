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

namespace Comely\Fluent\Database\Table\Columns;

use Comely\Fluent\Exception\FluentTableException;

/**
 * Class EnumColumn
 * @package Comely\Fluent\Database\Table\Columns
 * @property array $_opts
 */
class EnumColumn extends AbstractColumn
{
    /** @var array */
    private $options;

    /**
     * EnumColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "enum";
        $this->scalarType = "string";
        $this->options = [];
    }

    /**
     * @param string ...$options
     * @return EnumColumn
     */
    public function options(string ...$options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param $prop
     * @return bool|mixed
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_opts":
                return $this->options;
        }

        return parent::__get($prop);
    }

    /**
     * @param string $opt
     * @return EnumColumn
     * @throws FluentTableException
     */
    public function default(string $opt): self
    {
        if (!in_array($opt, $this->options)) {
            throw FluentTableException::ColumnError($this->name, 'Default value must be from defined ENUM set');
        }

        $this->setDefaultValue($opt);
        return $this;
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function columnSQL(string $driver): ?string
    {
        $options = implode(",", array_map(function (string $opt) {
            return sprintf("'%s'", $opt);
        }, $this->options));

        switch ($driver) {
            case "mysql":
                return sprintf('enum(%s)', $options);
            case "sqlite":
                return sprintf('TEXT CHECK(%s in (%s))', $this->name, $options);
        }

        return null;
    }
}