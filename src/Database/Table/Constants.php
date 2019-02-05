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

namespace Comely\Fluent\Database\Table;

/**
 * Interface Constants
 * @package Comely\Fluent\Database\Table
 */
interface Constants
{
    // Integer column constants
    // Correspond to size in bytes
    public const INT_TINY = 1;
    public const INT_SMALL = 2;
    public const INT_MEDIUM = 3;
    public const INT_DEFAULT = 4;
    public const INT_BIG = 8;

    // Text/Block column constants
    public const SIZE_TINY = "TINY";
    public const SIZE_DEFAULT = "";
    public const SIZE_MEDIUM = "MEDIUM";
    public const SIZE_LONG = "LONG";
}