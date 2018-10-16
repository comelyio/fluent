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

/**
 * Class ForeignConstraint
 * @package Comely\Fluent\Database\Table\Constraints
 * @property string $_table
 * @property string $_col
 */
class ForeignConstraint extends AbstractConstraint
{
    /** @var null|string */
    protected $table;
    /** @var null|string */
    protected $col;
    /** @var null|string */
    protected $db;

    /**
     * @param string $table
     * @param string $column
     * @return ForeignConstraint
     */
    public function table(string $table, string $column): self
    {
        $this->table = $table;
        $this->col = $column;
        return $this;
    }

    /**
     * @param string $db
     * @return ForeignConstraint
     */
    public function database(string $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param $prop
     * @return array|bool|null|string
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_table":
                return $this->table;
            case "_col":
                return $this->col;
        }

        return parent::__get($prop);
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function constraintSQL(string $driver): ?string
    {
        $tableReference = $this->db ? sprintf('`%s`.`%s`', $this->db, $this->table) : sprintf('`%s`', $this->table);
        switch ($driver) {
            case "mysql":
                return sprintf('FOREIGN KEY (`%s`) REFERENCES %s(`%s`)', $this->name, $tableReference, $this->col);
            case "sqlite":
                return sprintf(
                    'CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES %s(`%s`)',
                    sprintf('cnstrnt_%s_frgn', $this->name),
                    $this->name,
                    $tableReference,
                    $this->col
                );
        }

        return null;
    }
}