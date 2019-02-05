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

use Comely\Fluent\Database\Table\Traits\NullableColumnTrait;
use Comely\Fluent\Database\Table\Traits\StringSizeTrait;

/**
 * Class BlobColumn
 * @package Comely\Fluent\Database\Table\Columns
 */
class BlobColumn extends AbstractColumn
{
    /** @var null|string */
    private $size;

    use NullableColumnTrait;
    use StringSizeTrait;

    /**
     * TextColumn constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = "blob";
        $this->scalarType = "string";
        $this->size = ""; // default
    }

    /**
     * @param string $driver
     * @return null|string
     */
    protected function columnSQL(string $driver): ?string
    {
        switch ($driver) {
            case "mysql":
                return sprintf('%sBLOB', $this->size);
            case "sqlite":
            default:
                return "BLOB";
        }
    }
}