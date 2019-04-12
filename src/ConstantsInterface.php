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

namespace Comely\Fluent;

/**
 * Interface ConstantsInterface
 * @package Comely\Fluent
 */
interface ConstantsInterface
{
    /** string Comely Fluent Version (Major.Minor.Release-Suffix) */
    public const VERSION = "1.0.4";
    /** int Comely Fluent Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 10004;

    /** @var int Exception signal indicating no row was found matching query */
    public const SIGNAL_ROW_NOT_MATCH = 0x03E8;
}