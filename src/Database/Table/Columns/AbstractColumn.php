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

namespace Comely\Fluent\Database\Table\Columns;

use Comely\Fluent\Exception\FluentTableException;

/**
 * Class AbstractColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
abstract class AbstractColumn implements ColumnInterface
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $type;
    /** @var string */
    protected $scalarType;
    /** @var null|string|int */
    protected $default;
    /** @var array */
    protected $attributes;

    /**
     * AbstractColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->attributes = [];
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param $value
     * @return AbstractColumn
     * @throws FluentTableException
     */
    protected function setDefaultValue($value): self
    {
        if (is_null($value)) {
            $nullability = $this->attributes["nullable"] ?? false;
            if (!$nullability) {
                throw FluentTableException::ColumnError(
                    $this->name,
                    'Default cannot be NULL, column is not nullable'
                );
            }
        }

        $this->default = $value;
        return $this;
    }
}