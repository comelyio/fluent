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
 * Trait CharsetColumnTrait
 * @package Comely\Fluent\Database\Table\Traits
 */
trait CharsetColumnTrait
{
    /**
     * @param string $charset
     * @return self
     */
    final public function charset(string $charset): self
    {
        $this->attributes["charset"] = $charset;
        return $this;
    }

    /**
     * @param string $collate
     * @return self
     */
    final public function collation(string $collate): self
    {
        $this->attributes["collation"] = $collate;
        return $this;
    }
}