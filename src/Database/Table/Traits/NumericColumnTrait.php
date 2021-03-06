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

namespace Comely\Fluent\Database\Table\Traits;

/**
 * Trait NumericColumnTrait
 * @package Comely\Fluent\Database\Table\Traits
 */
trait NumericColumnTrait
{
    /**
     * @return self
     */
    final public function signed(): self
    {
        $this->attributes["unsigned"] = 0;
        return $this;
    }

    /**
     * @return self
     */
    final public function unSigned(): self
    {
        $this->attributes["unsigned"] = 1;
        return $this;
    }
}