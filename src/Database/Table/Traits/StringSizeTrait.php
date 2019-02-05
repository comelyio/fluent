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

use Comely\Fluent\Exception\FluentTableException;

/**
 * Trait StringSizeTrait
 * @package Comely\Fluent\Database\Table\Traits
 */
trait StringSizeTrait
{
    /**
     * @param string $size
     * @return self
     * @throws FluentTableException
     */
    final public function size(string $size): self
    {
        if (!in_array($size, ["TINY", "", "MEDIUM", "LONG"])) {
            throw FluentTableException::ColumnError($this->name, 'Bad column size, use Schema::SIZE_* flag');
        }

        $this->size = $size;
        return $this;
    }
}