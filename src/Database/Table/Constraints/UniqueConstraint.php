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

namespace Comely\Fluent\Database\Table\Constraints;

/**
 * Class UniqueConstraint
 * @package Comely\Fluent\Database\Table\Constraints
 * @property array $_cols
 */
class UniqueConstraint extends AbstractConstraint
{
    /** @var array */
    protected $columns;

    /**
     * UniqueConstraint constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->columns = [];
    }

    /**
     * @param string ...$cols
     * @return UniqueConstraint
     */
    public function columns(string ...$cols): self
    {
        $this->columns = $cols;
        return $this;
    }

    /**
     * @param $prop
     * @return array|bool
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_cols":
                return $this->columns;
        }

        return parent::__get($prop);
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function constraintSQL(string $driver): ?string
    {
        $columns = implode(",", array_map(function ($col) {
            return sprintf('`%s`', $col);
        }, $this->columns));

        switch ($driver) {
            case "mysql":
                return sprintf('UNIQUE KEY `%s` (%s)', $this->name, $columns);
            case "sqlite":
                return sprintf('CONSTRAINT `%s` UNIQUE (%s)', $this->name, $columns);
        }

        return null;
    }
}