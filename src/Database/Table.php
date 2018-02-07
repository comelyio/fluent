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

namespace Comely\Fluent\Database;

use Comely\Fluent\Database\Table\Constants;
use Comely\Fluent\Database\Table\Schema;
use Comely\IO\Database\Database;

/**
 * Class Table
 * @package Comely\Fluent\Database
 */
abstract class Table implements Constants
{
    /** @var Database */
    private $db;
    /** @var Schema */
    private $schema;

    /**
     * Table constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->schema = new Schema();

        // Callback schema method for table structure
        $this->schema($this->schema);
    }

    abstract public function schema(Schema $schema): void;

    private function validate(): void
    {

    }
}