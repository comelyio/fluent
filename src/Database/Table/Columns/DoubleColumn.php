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

use Comely\Fluent\Database\Table\Traits\NullableColumnTrait;
use Comely\Fluent\Database\Table\Traits\NumericColumnTrait;
use Comely\Fluent\Database\Table\Traits\PrecisionColumnTrait;

/**
 * Class DoubleColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
class DoubleColumn extends AbstractColumn
{
    const PRECISION_MAX = 65;
    const SCALE_MAX = 30;

    /** @var int */
    protected $digits;
    /** @var int */
    protected $scale;

    use NullableColumnTrait;
    use NumericColumnTrait;
    use PrecisionColumnTrait;

    /**
     * DecimalColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "double";
        $this->scalarType = "double";
        $this->digits = 10;
        $this->scale = 0;
        $this->default = 0;
    }

    /**
     * @param float|null $value
     * @return DoubleColumn
     * @throws \Comely\Fluent\Exception\FluentTableException
     */
    public function default(?float $value = 0): self
    {
        $this->setDefaultValue($value);
        return $this;
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function columnSQL(string $driver): ?string
    {
        switch ($driver) {
            case "mysql":
                return sprintf('%s(%d,%d)', $this->type, $this->digits, $this->scale);
            case "sqlite":
                return "REAL";
        }

        return null;
    }
}