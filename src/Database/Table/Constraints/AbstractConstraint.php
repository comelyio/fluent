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

namespace Comely\Fluent\Database\Table\Constraints;

use Comely\Fluent\Exception\FluentTableException;

/**
 * Class AbstractConstraint
 * @package Comely\Fluent\Database\Table\Constraints
 * @property string $_name
 * @method null|string getConstraintSQL (string $driver)
 */
abstract class AbstractConstraint implements ConstraintInterface
{
    /** @var string */
    protected $name;

    /**
     * AbstractConstraint constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param $prop
     * @return array|bool
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_name":
                return $this->name;
        }

        return false;
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
            case "getConstraintSQL":
                return $this->constraintSQL(strval($arguments[0] ?? ""));
        }

        throw new FluentTableException(sprintf('Calling inaccessible method on constraint "%s"', $this->name));
    }

    /**
     * @param string $driver
     * @return null|string
     */
    abstract protected function constraintSQL(string $driver): ?string;
}