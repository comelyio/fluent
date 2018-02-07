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

namespace Comely\Fluent\Database\Table;

use Comely\Fluent\Database\Table\Columns\AbstractColumn;
use Comely\Fluent\Database\Table\Columns\ColumnInterface;

/**
 * Class Columns
 * @package Comely\Fluent\Database\Table
 */
class Columns implements \Countable
{
    /** @var array */
    private $columns;
    /** @var int */
    private $count;
    /** @var int */
    private $index;

    /**
     * Columns constructor.
     */
    public function __construct()
    {
        $this->columns = [];
        $this->count = 0;
        $this->index = 0;
    }

    /**
     * @param ColumnInterface|AbstractColumn $column
     * @return ColumnInterface
     */
    public function append(ColumnInterface $column): ColumnInterface
    {
        $this->columns[$column->name()] = $column;
        $this->count++;
        return $column;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }
}