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
use Comely\Fluent\Exception\FluentTableException;

/**
 * Class Columns
 * @package Comely\Fluent\Database\Table
 * @property null|string $primary
 */
class Columns implements \Countable, \Iterator
{
    /** @var array */
    private $columns;
    /** @var int */
    private $count;
    /** @var string */
    private $defaultCharset;
    /** @var string */
    private $defaultCollate;
    /** @var null|string */
    private $primaryKey;

    /**
     * Columns constructor.
     */
    public function __construct()
    {
        $this->columns = [];
        $this->count = 0;
        $this->defaults("utf8mb4", "utf8mb4_unicode_ci");
    }

    /**
     * @param $name
     * @return bool|null|string
     */
    public function __get($name)
    {
        switch ($name) {
            case "primary":
                return $this->primaryKey;
        }

        return false;
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
        $this->columns[$column->_name] = $column;
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
    public function int(string $name): ColumnInterface
    {
        return $this->append(new IntegerColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|StringColumn
     */
    public function string(string $name): ColumnInterface
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
    public function binary(string $name): ColumnInterface
    {
        return $this->append(new BinaryColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|TextColumn
     */
    public function text(string $name): ColumnInterface
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
    public function blob(string $name): ColumnInterface
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
    public function float(string $name): ColumnInterface
    {
        return $this->append(new FloatColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|DoubleColumn
     */
    public function double(string $name): ColumnInterface
    {
        return $this->append(new DoubleColumn($name));
    }

    /**
     * @param string $col
     * @throws FluentTableException
     */
    public function primaryKey(string $col): void
    {
        $column = $this->columns[$col] ?? null;
        if (!$column) {
            throw new FluentTableException(
                sprintf('Cannot declare undefined "%s" column as primary key', $col)
            );
        }

        $this->primaryKey = $col;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->columns);
    }

    /**
     * @return ColumnInterface|AbstractColumn
     */
    public function current(): ColumnInterface
    {
        return current($this->columns);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->columns);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->columns);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return is_null(key($this->columns)) ? false : true;
    }
}