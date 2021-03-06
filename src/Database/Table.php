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

namespace Comely\Fluent\Database;

use Comely\Fluent\ConstantsInterface;
use Comely\Fluent\Database\Table\Columns;
use Comely\Fluent\Database\Table\Constants;
use Comely\Fluent\Database\Table\Constraints;
use Comely\Fluent\Exception\FluentException;
use Comely\Fluent\Exception\FluentTableException;
use Comely\Fluent\Fluent;
use Comely\Fluent\ORM\Model;
use Comely\IO\Database\Database;
use Comely\IO\Database\Exception\DatabaseException;
use Comely\Kernel\Traits\NotCloneableTrait;
use Comely\Kernel\Traits\NotSerializableTrait;

/**
 * Class Table
 * @package Comely\Fluent\Database
 * @property string $_name
 * @property string $_engine
 * @property string $_models
 * @method void callback()
 */
abstract class Table implements Constants, ConstantsInterface
{
    public const NAME = null;
    public const ENGINE = 'InnoDB';
    public const MODEL = null;

    use NotSerializableTrait;
    use NotCloneableTrait;

    /** @var Database */
    protected $db;
    /** @var Columns */
    protected $columns;
    /** @var Constraints */
    protected $constraints;
    /** @var string */
    protected $name;
    /** @var string */
    protected $engine;
    /** @var string */
    protected $modelsClass;

    /**
     * Table constructor.
     * @param Database $db
     * @throws FluentTableException
     */
    final public function __construct(Database $db)
    {
        $this->db = $db;
        $this->columns = new Columns();
        $this->constraints = new Constraints();

        // Get table names and engine
        $this->name = static::NAME;
        if (!is_string($this->name) || !preg_match('/^[a-zA-Z0-9\_]+$/', $this->name)) {
            throw new FluentTableException(sprintf('"%s" must define a valid NAME constant', get_called_class()));
        }

        $this->engine = static::ENGINE;
        if (!is_string($this->engine) || !preg_match('/^[a-zA-Z]+$/', $this->engine)) {
            throw new FluentTableException(sprintf('"%s" must define a valid ENGINE constant', get_called_class()));
        }

        // Models class
        $this->modelsClass = static::MODEL;
        if (!is_null($this->modelsClass)) {
            if (!is_string($this->modelsClass) || !preg_match('/^[a-zA-Z0-9\_\\\]+$/', $this->modelsClass)) {
                throw new FluentTableException(
                    sprintf('Provide a valid class name or NULL for MODEL constant for table "%s"', $this->name)
                );
            }

            // Make sure class exists and is loadable
            if (!class_exists($this->modelsClass)) {
                throw new FluentTableException(
                    sprintf(
                        'Models class "%s" not found for table "%s"',
                        $this->modelsClass,
                        $this->name
                    )
                );
            }
        }

        // Constructor callback
        if (method_exists($this, "callback")) {
            call_user_func([$this, "callback"]);
        }

        // Callback schema method for table structure
        $this->schema($this->columns, $this->constraints);
    }

    /**
     * @param $prop
     * @return bool|mixed
     */
    final public function __get($prop)
    {
        switch ($prop) {
            case "_name":
                return $this->name;
            case "_engine":
                return $this->engine;
            case "_models":
                return $this->modelsClass;
        }

        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @throws FluentTableException
     */
    final public function __set($prop, $value)
    {
        throw new FluentTableException('Overriding inaccessible properties not allowed');
    }


    /**
     * @param string $col
     * @param $value
     * @return Model
     * @throws FluentException
     * @throws FluentTableException
     */
    final public static function findBy(string $col, $value): Model
    {
        $table = Fluent::Retrieve(strval(static::NAME));
        $modelsClass = $table->_models;
        if (!$modelsClass) {
            throw new FluentException(sprintf('constant MODELS not defined for table "%s"', get_called_class()));
        }

        $column = $table->columns->get($col);
        try {
            $fetched = $table->db()->query()->table($table->_name)
                ->find(["" . $column->_name . "" => $value])
                ->limit(1)
                ->fetch();
        } catch (DatabaseException $e) {
            throw new FluentException($e->getMessage());
        }

        $first = $fetched->first();
        if (!$first) {
            throw new FluentException(
                sprintf('No row matching "%s" returned from table "%s"', $column->_name, $table->_name),
                self::SIGNAL_ROW_NOT_MATCH
            );
        }

        return new $modelsClass($first);
    }

    /**
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws FluentException
     */
    final public static function findWhere(string $query = "1", ?array $params = null): array
    {
        $table = Fluent::Retrieve(strval(static::NAME));
        $modelsClass = $table->_models;
        if (!$modelsClass) {
            throw new FluentException(sprintf('constant MODELS not defined for table "%s"', get_called_class()));
        }

        try {
            $rows = $table->db()->fetch(
                sprintf('SELECT' . ' * FROM `%s` WHERE %s', $table->_name, $query),
                $params ?? []
            );
        } catch (DatabaseException $e) {
            throw new FluentException($e->getMessage());
        }

        $models = [];
        if ($rows) {
            foreach ($rows as $row) {
                try {
                    $rowModel = new $modelsClass($row);
                    $models[] = $rowModel;
                } catch (FluentException $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        }

        return $models;
    }

    /**
     * @return Database
     */
    final public function db(): Database
    {
        return $this->db;
    }

    /**
     * @return Columns
     */
    final public function columns(): Columns
    {
        return $this->columns;
    }

    /**
     * @return Constraints
     */
    final public function constraints(): Constraints
    {
        return $this->constraints;
    }

    /**
     * @param Columns $cols
     * @param Constraints|null $constraints
     */
    abstract public function schema(Columns $cols, Constraints $constraints): void;
}