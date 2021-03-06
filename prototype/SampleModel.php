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

namespace App\Models;

use Comely\Fluent\ORM\FluentModel;

/**
 * Class SampleModel
 * @package App\Models
 */
class SampleModel extends FluentModel
{
    public const TABLE = 'App\Database\SampleTable';

    /** @var int */
    public $id;
    /** @var int */
    public $status;
    /** @var string */
    public $role;
    /** @var string */
    public $emailAddress;
    /** @var string */
    public $country;
    /** @var int */
    public $points;
    /** @var int */
    public $timeStamp;
}