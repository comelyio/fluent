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

use Comely\Fluent\Database\Table\Traits\NumericColumnTrait;
use Comely\Fluent\Database\Table\Traits\PrecisionColumnTrait;
use Comely\Fluent\Exception\FluentTableException;

/**
 * Class DecimalColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
class DecimalColumn extends AbstractColumn
{
    const PRECISION_MAX = 65;
    const SCALE_MAX = 30;

    /** @var int */
    protected $digits;
    /** @var int */
    protected $scale;

    use NumericColumnTrait;
    use PrecisionColumnTrait;

    /**
     * DecimalColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "decimal";
        $this->scalarType = "string";
        $this->digits = 10;
        $this->scale = 0;
        $this->default = "0";
    }

    /**
     * @param string $value
     * @return DecimalColumn
     * @throws \Comely\Fluent\Exception\FluentTableException
     */
    public function default(string $value = "0"): self
    {
        if (!preg_match('/^\-?[0-9]+(\.[0-9]+)?$/', $value)) {
            throw FluentTableException::ColumnError($this->name, 'Bad decimal default value');
        }

        $this->setDefaultValue($value);
        return $this;
    }
}