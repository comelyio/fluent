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
use Comely\Fluent\Database\Table\Traits\UniqueColumnTrait;
use Comely\Fluent\Exception\FluentTableException;

/**
 * Class IntegerColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
class IntegerColumn extends AbstractColumn
{
    /** @var int */
    protected $bytes;
    /** @var null|int */
    protected $digits;
    /** @var bool */
    protected $autoIncrement;

    use NullableColumnTrait;
    use NumericColumnTrait;
    use UniqueColumnTrait;

    /**
     * IntegerColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "int";
        $this->scalarType = "integer";
        $this->attributes["nullable"] = false;
        $this->attributes["signed"] = 1; // Signed integer
        $this->bytes = 4; // Default; 4 byte integer
        $this->autoIncrement = false;
    }

    /**
     * @param int $bytes
     * @param int|null $digits
     * @return IntegerColumn
     * @throws FluentTableException
     */
    public function size(int $bytes, int $digits = null): self
    {
        // Bytes
        if (!in_array($bytes, [1, 2, 3, 4, 8])) {
            throw FluentTableException::ColumnError($this->name, 'Bad integer size in bytes, use Schema::INT_* flag');
        }

        $this->bytes = $bytes;

        // Digits
        $this->digits = null;
        if ($digits) {
            $maxDigits = 10; // 4 byte integer
            switch ($this->bytes) {
                case 1:
                    $maxDigits = 3;
                    break;
                case 2:
                    $maxDigits = 5;
                    break;
                case 3:
                    $maxDigits = $this->attributes["signed"] ? 7 : 8;
                    break;
                case 8:
                    $maxDigits = $this->attributes["signed"] ? 19 : 20;
                    break;
            }

            // Check if digits can be stored in specified bytes size
            if ($digits > $maxDigits) {
                throw FluentTableException::ColumnError(
                    $this->name,
                    sprintf('%d byte integer can hold maximum of %d digits', $this->bytes, $maxDigits)
                );
            }

            // Set digits
            $this->digits = $digits;
        }

        return $this;
    }

    /**
     * @return IntegerColumn
     */
    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * @param int|null $value
     * @return IntegerColumn
     * @throws FluentTableException
     */
    public function default(?int $value): self
    {
        $this->setDefaultValue($value);
        return $this;
    }
}