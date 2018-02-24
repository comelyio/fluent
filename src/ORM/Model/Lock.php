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

namespace Comely\Fluent\ORM\Model;

use Comely\Fluent\Database\Table\Columns\AbstractColumn;
use Comely\Fluent\Exception\FluentModelException;
use Comely\Fluent\Exception\FluentTableException;
use Comely\Fluent\Exception\ModelLockException;
use Comely\Fluent\ORM\Model;
use Comely\IO\Database\Exception\DatabaseException;
use Comely\Kernel\Comely;

/**
 * Class Lock
 * @package Comely\Fluent\ORM\Model
 */
class Lock
{
    /** @var bool */
    private $status;
    /** @var Model */
    private $model;
    /** @var AbstractColumn */
    private $matchColumn;
    /** @var mixed */
    private $matchValue;

    /**
     * Lock constructor.
     * @param Model $model
     * @throws ModelLockException
     */
    public function __construct(Model $model)
    {
        $this->status = false;
        $this->model = $model;

        $this->matchColumn = $this->model->getPrimaryColumn();
        if (!$this->matchColumn) {
            throw new ModelLockException(
                sprintf('Cannot obtain lock on "%s" table has no PRIMARY/UNIQUE key', $this->model->table()->_name)
            );
        }

        try {
            $this->matchValue = $this->model->get(Comely::camelCase($this->matchColumn->_name));
            $this->model->validateColumnValue($this->matchColumn, $this->matchColumn->_name, $this->matchValue);
        } catch (FluentModelException $e) {
            throw new ModelLockException($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function status(): bool
    {
        return $this->status;
    }

    /**
     * @param null|string $crosscheckCol
     * @param null $value
     * @return Lock
     * @throws ModelLockException
     */
    public function obtain(?string $crosscheckCol = null, $value = null): self
    {
        $table = $this->model->table();
        $selectColumns[] = sprintf('`%s`', $this->matchColumn->_name);

        // Cross-checking column
        if ($crosscheckCol) {
            // Check if crosscheck column exists
            try {
                $crosscheckColumn = $table->columns()->get($crosscheckCol);
            } catch (FluentTableException $e) {
                throw new ModelLockException(
                    sprintf('Cannot cross-check lock on "%s" with undefined column "%s"', $table->_name, $crosscheckCol)
                );
            }

            $crosscheckValue = $value;
            unset($crosscheckCol, $value);

            try {
                $this->model->validateColumnValue($crosscheckColumn, $crosscheckColumn->_name, $crosscheckValue);
            } catch (FluentModelException $e) {
                throw new ModelLockException($e->getMessage());
            }

            $selectColumns[] = sprintf('`%s`', $crosscheckColumn->_name);
        }

        // Obtain SELECT ... FOR UPDATE lock
        $query = sprintf(
            'SELECT' . ' %s FROM `%s` WHERE `%s`=? FOR UPDATE',
            implode(",", $selectColumns),
            $table->_name,
            $this->matchColumn->_name
        );

        try {
            $fetch = $table->db()->fetch($query, [$this->matchValue]);
        } catch (DatabaseException $e) {
            throw new ModelLockException($e->getMessage());
        }

        // Cross-checking?
        if (isset($crosscheckColumn, $crosscheckValue)) {
            $fetched = $fetch[0][$crosscheckColumn->_name];
            if (!array_key_exists($crosscheckColumn->_name, $fetched[0] ?? []) || $fetched !== $crosscheckValue) {
                throw new ModelLockException(
                    sprintf(
                        'Cross-checking "%s" failed on lock obtain in table "%s"',
                        $crosscheckColumn->_name,
                        $table->_name
                    )
                );
            }
        }

        $this->status = true;
        return $this;
    }
}