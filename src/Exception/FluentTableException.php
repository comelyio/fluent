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
 * Class FluentTableException
 * @package Comely\Fluent\Exception
 */
class FluentTableException extends FluentException
{
    /**
     * @param string $col
     * @param string $message
     * @return FluentTableException
     */
    public static function ColumnError(string $col, string $message): self
    {
        return new self(sprintf('%s for column "%s"', $message, $col));
    }
}