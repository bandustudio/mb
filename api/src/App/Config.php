<?php

/*
 * This file is part of the Slim API skeleton package
 *
 * Copyright (c) 2016 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-api-skeleton
 *
 */

namespace App;

use Spot\EntityInterface as Entity;
use Spot\MapperInterface as Mapper;
use Spot\EventEmitter;

use Tuupola\Base62;

use Ramsey\Uuid\Uuid;
use Psr\Log\LogLevel;

class Config extends \Spot\Entity
{
    protected static $table = "config";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "config_key" => ["type" => "string", "length" => 50],
            "config_value" => ["type" => "text"],
            "enabled" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Config $region)
    {
        return [
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "config_key" => null,
            "config_value" => null,
            "enabled" => null
        ]);
    }
}
