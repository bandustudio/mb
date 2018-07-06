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

class Upload extends \Spot\Entity
{

    protected static $table = "uploads";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "file_url" => ["type" => "string", "length" => 255],
            "lead_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true],
            "user_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true],
            "position" => ["type" => "integer", "unsigned" => true, "value" => 0],
            "filesize" => ["type" => "integer", "unsigned" => true, "value" => 0],
            "enabled" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            //'lead' => $mapper->belongsTo($entity, 'App\Lead', 'lead_id'),
        ];
    }

    public function transform(Upload $photo)
    {
        return [
            "id" => (integer) $photo->id ?: null,
            "file_url" => (string) $photo->photo_url ?: ""
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "title" => null,
            "enabled" => null
        ]);
    }
}
