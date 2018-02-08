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

namespace App\Database;

use Comely\Fluent\Database\Table;
use Comely\Fluent\Database\Table\Columns;
use Comely\Fluent\Database\Table\Constraints;

/**
 * Class SampleTable
 * @package App\Database
 */
class SampleTable extends Table
{
    /**
     * @param Columns $cols
     * @param Constraints|null $constraints
     * @throws \Comely\Fluent\Exception\FluentTableException
     */
    public function schema(Columns $cols, Constraints $constraints): void
    {
        $cols->int("id")->size(self::INT_MEDIUM)->unSigned()->autoIncrement();
        $cols->string("email")->length(255)->unique();
        $cols->string("password")->fixed(40)->nullable()->default(null);
        $cols->binary("token")->fixed(10)->nullable()->default(null);
        $cols->string("country")->fixed(3)->nullable();
        $cols->int("time_stamp")->size(self::INT_DEFAULT);
        $cols->primaryKey("id");

        $constraints->uniqueKey("email-id")->columns("email", "id");
        $constraints->foreignKey("country")->table("countries", "code");
    }
}