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

namespace Comely\Fluent\Database\Table\Traits;

use Comely\Fluent\Exception\FluentTableException;
use Comely\Kernel\Toolkit\Number;

/**
 * Trait LengthColumnTrait
 * @package Comely\Fluent\Database\Table\Traits
 */
trait LengthColumnTrait
{
    /**
     * @param int $length
     * @return self
     * @throws FluentTableException
     */
    public function length(int $length): self
    {
        if (!Number::Range($length, self::LENGTH_MIN, self::LENGTH_MAX)) {
            throw FluentTableException::ColumnError(
                $this->name,
                sprintf(
                    'Maximum storage length must be between %d and %d %s',
                    self::LENGTH_MIN,
                    self::LENGTH_MAX,
                    self::LENGTH_UNIT
                )
            );
        }

        $this->length = $length;
        return $this;
    }

    /**
     * @param int $length
     * @return self
     * @throws FluentTableException
     */
    public function fixed(int $length): self
    {
        $this->length($length);
        $this->fixed = true;
        return $this;
    }
}