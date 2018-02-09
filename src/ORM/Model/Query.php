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

namespace Comely\Fluent\ORM\Model;

use Comely\Fluent\Exception\FluentException;
use Comely\Fluent\Exception\FluentModelException;
use Comely\Fluent\Exception\ModelQueryException;
use Comely\Fluent\ORM\Model;
use Comely\IO\Database\Exception\DatabaseException;

/**
 * Class Query
 * @package Comely\Fluent\ORM\Model
 */
class Query
{
    /** @var Model */
    private $model;
    /** @var array */
    private $changes;
    /** @var null|string */
    private $matchColumn;
    /** @var null|string|int|float */
    private $matchValue;

    /**
     * Query constructor.
     * @param Model $model
     * @throws ModelQueryException
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        try {
            $this->changes = $model->difference();
        } catch (FluentModelException $e) {
            throw new ModelQueryException($e->getMessage());
        }

        $primaryColumn = $model->getPrimaryColumn();
        if ($primaryColumn) {
            $this->matchColumn = $primaryColumn->_name;
            $this->matchValue = $this->changes[$this->matchColumn] ?? null;
        }
    }

    /**
     * @param string $col
     * @param null $value
     * @return Query
     * @throws ModelQueryException
     */
    public function where(string $col, $value = null): self
    {
        // Check column and validate value
        try {
            $column = $this->model->table()->columns()->get($col);
            $this->model->validateColumnValue($column, $column->_name, $value);
        } catch (FluentException $e) {
            throw new ModelQueryException($e->getMessage());
        }

        // Make sure it is a PRIMARY or UNIQUE column
        if ($this->model->table()->columns()->_primary !== $column->_name) {
            if (!isset($column->_attrs["unique"])) {
                throw new ModelQueryException(
                    sprintf(
                        '"%s" is not a PRIMARY/UNIQUE column for table "%s"',
                        $column->_name,
                        $this->model->table()->_name
                    )
                );
            }
        }

        $this->matchColumn = $column->_name;
        $this->matchValue = $value;
        return $this;
    }

    /**
     * @param string $query
     * @throws ModelQueryException
     */
    private function validateMatchClause(string $query): void
    {
        if (!$this->matchColumn) {
            throw new ModelQueryException(
                sprintf('"%s" %s query requires a PRIMARY/UNIQUE column', $this->model->name(), strtoupper($query))
            );
        }

        if (!$this->matchValue) {
            throw new ModelQueryException(
                sprintf(
                    '"%s" cannot be empty/NULL for %s "%s" query',
                    $this->matchColumn,
                    strtoupper($query),
                    $this->model->name()
                )
            );
        }
    }

    /**
     * @param callable|null $callback
     * @return bool
     * @throws ModelQueryException
     */
    public function save(?callable $callback = null): bool
    {
        $this->validateMatchClause("save");
        $table = $this->model->table();

        $insertColumns = [];
        $insertParams = [];
        $updateParams = [];
        foreach ($this->changes as $key => $value) {
            $insertColumns[] = sprintf('`%s`', $key);
            $insertParams[] = ":" . $key;
            $updateParams[] = sprintf('`%1$s`=:%1$s', $key);
        }

        $query = sprintf(
            'INSERT' . ' INTO `%s` (%s) VALUES (%s)  ON DUPLICATE KEY UPDATE %s',
            $table->_name,
            implode(", ", $insertColumns),
            implode(", ", $insertParams),
            implode(", ", $updateParams)
        );


        try {
            $table->db()->exec($query, ...$this->changes);
        } catch (DatabaseException $e) {
            throw ModelQueryException::QueryFailed("save", $table->_name);
        }

        // Callback?
        $lastQuery = $table->db()->lastQuery();
        if (is_callable($callback)) {
            $callback($table->db()->lastQuery());
        }

        return $lastQuery->rows ? true : false;
    }

    /**
     * @param callable|null $callback
     * @return bool
     * @throws ModelQueryException
     */
    public function insert(?callable $callback = null): bool
    {
        $table = $this->model->table();
        $original = $this->model->original();
        if (count($original)) {
            throw new ModelQueryException(
                sprintf('INSERT query cannot be used on already existing "%s" row', $table->_name)
            );
        }

        $insertColumns = [];
        $insertParams = [];
        foreach ($this->changes as $key => $value) {
            $insertColumns[] = sprintf('`%s`', $key);
            $insertParams[] = ":" . $key;
        }

        $query = sprintf(
            'INSERT' . ' INTO `%s` (%s) VALUES (%s)',
            $table->_name,
            implode(", ", $insertColumns),
            implode(", ", $insertParams)
        );

        try {
            $table->db()->exec($query, ...$this->changes);
        } catch (DatabaseException $e) {
            throw ModelQueryException::QueryFailed("insert", $table->_name);
        }

        // Callback?
        $lastQuery = $table->db()->lastQuery();
        if (is_callable($callback)) {
            $callback($table->db()->lastQuery());
        }

        return $lastQuery->rows ? true : false;
    }

    /**
     * @param callable|null $callback
     * @return bool
     * @throws ModelQueryException
     */
    public function update(?callable $callback = null): bool
    {
        $this->validateMatchClause("update");
        $table = $this->model->table();

        if (!count($this->changes)) {
            throw new ModelQueryException(sprintf('There are no changes to UPDATE in "%s" row', $table->_name));
        }

        $updateParams = [];
        $updateValues = [];
        foreach ($this->changes as $key => $value) {
            $updateParams[] = sprintf('`%1$s`=:%1$s', $key);
            $updateValues[$key] = $value;
        }

        $updateValues["p_" . $this->matchColumn] = $this->matchValue;
        $query = sprintf(
            'UPDATE' . ' `%1$s` SET %2$s WHERE `%3$s`=:p_%3$s',
            $table->_name,
            implode(", ", $updateParams),
            $this->matchColumn
        );

        try {
            $table->db()->exec($query, ...$updateValues);
        } catch (DatabaseException $e) {
            throw ModelQueryException::QueryFailed("update", $table->_name);
        }

        // Callback?
        $lastQuery = $table->db()->lastQuery();
        if (is_callable($callback)) {
            $callback($lastQuery);
        }

        return $lastQuery->rows ? true : false;
    }

    /**
     * @param callable|null $callback
     * @return bool
     * @throws ModelQueryException
     */
    public function delete(?callable $callback = null): bool
    {
        $this->validateMatchClause("delete");
        $table = $this->model->table();
        $query = sprintf('DELETE' . ' FROM `%s` WHERE `%s`=?', $table->_name, $this->matchColumn);

        try {
            $table->db()->exec($query, $this->matchValue);
        } catch (DatabaseException $e) {
            throw ModelQueryException::QueryFailed("delete", $table->_name);
        }

        // Callback?
        $lastQuery = $table->db()->lastQuery();
        if (is_callable($callback)) {
            $callback($lastQuery);
        }

        return $lastQuery->rows ? true : false;
    }
}