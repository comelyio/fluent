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
use Comely\Fluent\Database\Table\Columns\BinaryColumn;
use Comely\Fluent\Database\Table\Columns\BlobColumn;
use Comely\Fluent\Database\Table\Columns\ColumnInterface;
use Comely\Fluent\Database\Table\Columns\DecimalColumn;
use Comely\Fluent\Database\Table\Columns\DoubleColumn;
use Comely\Fluent\Database\Table\Columns\FloatColumn;
use Comely\Fluent\Database\Table\Columns\IntegerColumn;
use Comely\Fluent\Database\Table\Columns\StringColumn;
use Comely\Fluent\Database\Table\Columns\TextColumn;

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
    /** @var string */
    private $defaultCharset;
    /** @var string */
    private $defaultCollate;

    /**
     * Columns constructor.
     */
    public function __construct()
    {
        $this->columns = [];
        $this->count = 0;
        $this->index = 0;
        $this->defaults("utf8mb4", "utf8mb4_unicode_ci");
    }

    /**
     * @param string $charset
     * @param string $collate
     * @return Columns
     */
    final public function defaults(string $charset, string $collate): self
    {
        $this->defaultCharset = $charset;
        $this->defaultCollate = $collate;
        return $this;
    }

    /**
     * @param ColumnInterface|AbstractColumn $column
     * @return ColumnInterface
     */
    private function append(ColumnInterface $column): ColumnInterface
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

    /**
     * @param string $name
     * @return ColumnInterface|IntegerColumn
     */
    final public function int(string $name): ColumnInterface
    {
        return $this->append(new IntegerColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|StringColumn
     */
    final public function string(string $name): ColumnInterface
    {
        /** @var StringColumn $col */
        $col = $this->append(new StringColumn($name));
        $col->charset($this->defaultCharset)
            ->collation($this->defaultCollate);
        return $col;
    }

    /**
     * @param string $name
     * @return ColumnInterface|BinaryColumn
     */
    final public function binary(string $name): ColumnInterface
    {
        return $this->append(new BinaryColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|TextColumn
     */
    final public function text(string $name): ColumnInterface
    {
        /** @var TextColumn $col */
        $col = $this->append(new TextColumn($name));
        $col->charset($this->defaultCharset)
            ->collation($this->defaultCollate);
        return $col;
    }

    /**
     * @param string $name
     * @return ColumnInterface|BlobColumn
     */
    final public function blob(string $name): ColumnInterface
    {
        return $this->append(new BlobColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|DecimalColumn
     */
    final public function decimal(string $name): ColumnInterface
    {
        return $this->append(new DecimalColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|FloatColumn
     */
    final public function float(string $name): ColumnInterface
    {
        return $this->append(new FloatColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|DoubleColumn
     */
    final public function double(string $name): ColumnInterface
    {
        return $this->append(new DoubleColumn($name));
    }
}