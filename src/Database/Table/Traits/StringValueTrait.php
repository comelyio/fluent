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

/**
 * Trait StringValueTrait
 * @package Comely\Fluent\Database\Table\Traits
 */
trait StringValueTrait
{
    /**
     * @param null|string $value
     * @return self
     * @throws \Comely\Fluent\Exception\FluentTableException
     */
    final public function default(?string $value): self
    {
        $this->setDefaultValue($value);
        return $this;
    }
}