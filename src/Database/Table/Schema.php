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
use Comely\Fluent\Database\Table\Columns\IntegerColumn;
use Comely\Fluent\Database\Table\Columns\StringColumn;
use Comely\Fluent\Database\Table\Columns\TextColumn;

/**
 * Class Schema
 * @package Comely\Fluent\Database\Table
 */
class Schema implements Constants
{
    /** @var Columns */
    private $columns;
    /** @var string */
    private $defaultCharset;
    /** @var string */
    private $defaultCollate;
    /** @var string */
    private $engine;

    /**
     * Schema constructor.
     */
    final public function __construct()
    {
        $this->columns = new Columns();
        $this->engine = "InnoDB";
        $this->defaults("utf8mb4", "utf8mb4_unicode_ci");
    }

    /**
     * @param string $charset
     * @param string $collate
     * @return Schema
     */
    final public function defaults(string $charset, string $collate): self
    {
        $this->defaultCharset = $charset;
        $this->defaultCollate = $collate;
        return $this;
    }

    /**
     * @param string $engine
     * @return Schema
     */
    final public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @param string $name
     * @return ColumnInterface|IntegerColumn
     */
    final public function int(string $name): ColumnInterface
    {
        return $this->columns->append(new IntegerColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|StringColumn
     */
    final public function string(string $name): ColumnInterface
    {
        /** @var StringColumn $col */
        $col = $this->columns->append(new StringColumn($name));
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
        return $this->columns->append(new BinaryColumn($name));
    }

    /**
     * @param string $name
     * @return ColumnInterface|TextColumn
     */
    final public function text(string $name): ColumnInterface
    {
        /** @var TextColumn $col */
        $col = $this->columns->append(new TextColumn($name));
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
        return $this->columns->append(new BlobColumn($name));
    }
}