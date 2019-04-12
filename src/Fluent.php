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

namespace Comely\Fluent;

use Comely\Fluent\Database\Table;
use Comely\Fluent\Exception\FluentException;
use Comely\IO\Database\Database;

/**
 * Class Fluent
 * @package Comely\Fluent
 */
class Fluent implements ConstantsInterface
{
    /** @var array */
    private static $tables = [];
    /** @var array */
    private static $index = [];

    /**
     * @param Database $db
     * @param string $table
     * @throws FluentException
     */
    public static function Bind(Database $db, string $table): void
    {
        if (!class_exists($table)) {
            throw new FluentException(sprintf('FluentTable class "%s" not found', $table));
        } elseif (!is_subclass_of($table, 'Comely\Fluent\Database\Table', true)) {
            throw new FluentException(sprintf('Class "%s" is not FluentTable', $table));
        }

        self::Append(new $table($db));
    }

    /**
     * @param Table $table
     */
    public static function Append(Table $table): void
    {
        self::$tables[$table->_name] = $table;
        self::$index[get_class($table)] = $table->_name;
    }

    /**
     * @param string $name
     * @return Table
     * @throws FluentException
     */
    public static function Retrieve(string $name): Table
    {
        $table = self::$tables[$name] ?? self::$tables[self::$index[$name] ?? ""] ?? null;
        if (!$table) {
            throw new FluentException(sprintf('Table "%s" is not bound with Fluent', $name));
        }

        return $table;
    }
}