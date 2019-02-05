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

namespace Comely\Fluent\Exception;

/**
 * Class ModelQueryException
 * @package Comely\Fluent\Exception
 */
class ModelQueryException extends FluentModelException
{
    const QUERY_DB_ERROR = 200;

    /**
     * @param string $query
     * @param string $table
     * @return ModelQueryException
     */
    public static function QueryFailed(string $query, string $table): self
    {
        return new self(
            sprintf('"%s" query failed on "%s" table', strtoupper($query), $table),
            self::QUERY_DB_ERROR
        );
    }
}