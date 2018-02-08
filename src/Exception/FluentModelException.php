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

namespace Comely\Fluent\Exception;

/**
 * Class FluentModelException
 * @package Comely\Fluent\Exception
 */
class FluentModelException extends FluentException
{
    /**
     * @param string $model
     * @param string $prop
     * @param string $message
     * @return FluentModelException
     */
    public static function BadValue(string $model, string $prop, string $message) : self
    {
        return new self(sprintf('Property "%s" of FluentModel "%s" %s', $prop, $model, $message));
    }
}