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
 * Trait PrecisionColumnTrait
 * @package Comely\Fluent\Database\Table\Traits
 * @property int $_digits
 * @property int $_scale
 */
trait PrecisionColumnTrait
{
    /**
     * @param int $digits
     * @param int $scale
     * @return self
     * @throws FluentTableException
     */
    public function precision(int $digits, int $scale): self
    {
        // Precision digits
        if (!Number::Range($digits, 1, self::PRECISION_MAX)) {
            throw FluentTableException::ColumnError(
                $this->name,
                sprintf('Precision digits must be between 1 and %d', self::PRECISION_MAX)
            );
        }

        // Scale
        $maxScale = $digits;
        if ($maxScale > self::SCALE_MAX) {
            $maxScale = self::SCALE_MAX;
        }

        if (!Number::Range($scale, 0, $maxScale)) {
            throw FluentTableException::ColumnError(
                $this->name,
                sprintf('Scale digits must be between 0 and %d', $maxScale)
            );
        }


        // Set
        $this->digits = $digits;
        $this->scale = $scale;
        return $this;
    }

    /**
     * @param $prop
     * @return mixed
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_digits":
                return $this->digits;
            case "_scale":
                return $this->scale;
        }

        /** @noinspection PhpUndefinedClassInspection */
        return parent::__get($prop);
    }
}