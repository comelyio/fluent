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

namespace Comely\Fluent\Database;

use Comely\Fluent\Database\Table\Columns\IntegerColumn;
use Comely\Fluent\Exception\TableBuilderException;

/**
 * Class TableBuilder
 * @package Comely\Fluent\Database
 */
class TableBuilder
{
    /** @var Table */
    private $table;
    /** @var bool */
    private $dropExisting;
    /** @var bool */
    private $createIfNotExists;
    /** @var string */
    private $eolChar;

    /**
     * TableBuilder constructor.
     * @param Table $table
     */
    private function __construct(Table $table)
    {
        $this->table = $table;
        $this->dropExisting = false;
        $this->createIfNotExists = false;
        $this->eolChar = PHP_EOL;
    }

    /**
     * @param Table $table
     * @return TableBuilder
     */
    public static function Create(Table $table) : self
    {
        return new TableBuilder($table);
    }

    /**
     * @return TableBuilder
     */
    public function dropExisting(): self
    {
        $this->dropExisting = true;
        return $this;
    }

    /**
     * @return TableBuilder
     */
    public function createIfNotExists(): self
    {
        $this->createIfNotExists = true;
        return $this;
    }

    /**
     * @param string $eol
     * @return TableBuilder
     * @throws TableBuilderException
     */
    public function setEOL(string $eol): self
    {
        if (!in_array($eol, ["\n", "\r\n"])) {
            throw new TableBuilderException(sprintf('"%s" is not a valid EOL character', $eol));
        }

        $this->eolChar = $eol;
        return $this;
    }

    /**
     * @return string
     */
    public function query(): string
    {
        $dbDriver = $this->table->db()->connection()->driverName;
        $statement = "";

        // Drop existing?
        if ($this->dropExisting) {
            $statement .= sprintf('DROP' . ' TABLE IF EXISTS `%s`;%s', $this->table->_name, $this->eolChar);
        }

        // Create statement
        $statement .= "CREATE TABLE";

        // Create if not exists?
        if ($this->createIfNotExists) {
            $statement .= " IF NOT EXISTS";
        }

        // Continue...
        $statement .= sprintf(' `%s` (%s', $this->table->_name, $this->eolChar);
        $columns = $this->table->columns();
        $primaryKey = $columns->_primary;
        $mysqlUniqueKeys = [];

        foreach ($columns as $column) {
            $statement .= sprintf('  `%s` %s', $column->_name, $column->getColumnSQL($dbDriver));

            // Signed or Unsigned
            if (isset($column->_attrs["unsigned"])) {
                if ($column->_attrs["unsigned"] === 1) {
                    if ($column instanceof IntegerColumn) {
                        if ($dbDriver === "sqlite" && $column->_ai) {
                            // SQLite auto-increment columns can't be unsigned
                        } else {
                            $statement .= " UNSIGNED";
                        }
                    } else {
                        $statement .= " UNSIGNED";
                    }
                }
            }

            // Primary Key
            if ($column->_name === $primaryKey) {
                $statement .= " PRIMARY KEY";
            }

            // Auto-increment
            if ($column instanceof IntegerColumn) {
                if ($column->_ai) {
                    switch ($dbDriver) {
                        case "mysql":
                            $statement .= " auto_increment";
                            break;
                        case "sqlite":
                            $statement .= " AUTOINCREMENT";
                            break;
                    }
                }
            }

            // Unique
            if (isset($column->_attrs["unique"])) {
                switch ($dbDriver) {
                    case "mysql":
                        $mysqlUniqueKeys[] = $column->_name;
                        break;
                    case "sqlite":
                        $statement .= " UNIQUE";
                        break;
                }
            }

            // MySQL specific attributes
            if ($dbDriver === "mysql") {
                if (isset($column->_attrs["charset"])) {
                    $statement .= " CHARACTER SET " . $column->_attrs["charset"];
                }

                if (isset($column->_attrs["collation"])) {
                    $statement .= " COLLATE " . $column->_attrs["collation"];
                }
            }

            // Nullable?
            if (!isset($column->_attrs["nullable"])) {
                $statement .= " NOT NULL";
            }

            // Default value
            if (is_null($column->_default)) {
                if (isset($column->_attrs["nullable"])) {
                    $statement .= " default NULL";
                }
            } else {
                $statement .= " default ";
                $statement .= is_string($column->_default) ? sprintf("'%s'", $column->_default) : $column->_default;
            }

            // EOL
            $statement .= "," . $this->eolChar;
        }

        // MySQL Unique Keys
        if ($dbDriver === "mysql") {
            foreach ($mysqlUniqueKeys as $mysqlUniqueKey) {
                $statement .= sprintf('  UNIQUE KEY (`%s`),%s', $mysqlUniqueKey, $this->eolChar);
            }
        }

        // Constraints
        foreach ($this->table->constraints() as $constraint) {
            $statement .= sprintf('  %s,%s', $constraint->getConstraintSQL($dbDriver), $this->eolChar);
        }

        // Finishing
        $statement = substr($statement, 0, -1 * (1 + strlen($this->eolChar))) . $this->eolChar;
        switch ($dbDriver) {
            case "mysql":
                $statement .= sprintf(') ENGINE=%s;', $this->table->_engine);
                break;
            case "sqlite":
            default:
                $statement .= ");";
                break;
        }

        return $statement;
    }
}