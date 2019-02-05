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

namespace Comely\Fluent\Database\Table\Columns;

use Comely\Fluent\Database\Table\Traits\CharsetColumnTrait;
use Comely\Fluent\Database\Table\Traits\LengthColumnTrait;
use Comely\Fluent\Database\Table\Traits\NullableColumnTrait;
use Comely\Fluent\Database\Table\Traits\StringValueTrait;
use Comely\Fluent\Database\Table\Traits\UniqueColumnTrait;

/**
 * Class StringColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
class StringColumn extends AbstractColumn
{
    const LENGTH_MIN = 1;
    const LENGTH_MAX = 65535;
    const LENGTH_UNIT = "chars";

    /** @var int */
    protected $length;
    /** @var bool */
    protected $fixed;

    use CharsetColumnTrait;
    use LengthColumnTrait;
    use NullableColumnTrait;
    use StringValueTrait;
    use UniqueColumnTrait;

    /**
     * StringColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "string";
        $this->scalarType = "string";
        $this->length = 255;
        $this->fixed = false;
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function columnSQL(string $driver): ?string
    {
        switch ($driver) {
            case "mysql":
                $type = $this->fixed ? "char" : "varchar";
                return sprintf('%s(%d)', $type, $this->length);
            case "sqlite":
            default:
                return "TEXT";
        }
    }
}