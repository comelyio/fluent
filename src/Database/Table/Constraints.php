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

namespace Comely\Fluent\Database\Table;

use Comely\Fluent\Database\Table\Constraints\AbstractConstraint;
use Comely\Fluent\Database\Table\Constraints\ConstraintInterface;
use Comely\Fluent\Database\Table\Constraints\ForeignConstraint;
use Comely\Fluent\Database\Table\Constraints\UniqueConstraint;

/**
 * Class Constraints
 * @package Comely\Fluent\Database\Table
 */
class Constraints implements \Countable, \Iterator
{
    /** @var array */
    private $constraints;
    /** @var int */
    private $count;

    /**
     * Constraints constructor.
     */
    public function __construct()
    {
        $this->constraints = [];
        $this->count = 0;
    }

    /**
     * @param string $key
     * @return UniqueConstraint
     */
    public function uniqueKey(string $key): UniqueConstraint
    {
        $constraint = new UniqueConstraint($key);
        $this->constraints[$key] = $constraint;
        $this->count++;
        return $constraint;
    }

    /**
     * @param string $key
     * @return ForeignConstraint
     */
    public function foreignKey(string $key): ForeignConstraint
    {
        $constraint = new ForeignConstraint($key);
        $this->constraints[$key] = $constraint;
        $this->count++;
        return $constraint;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->constraints);
    }

    /**
     * @return ConstraintInterface|AbstractConstraint
     */
    public function current(): ConstraintInterface
    {
        return current($this->constraints);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->constraints);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->constraints);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return is_null(key($this->constraints)) ? false : true;
    }
}