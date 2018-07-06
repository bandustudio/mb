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

class History extends \Spot\Entity
{
    protected static $table = "history";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "user_id" => ["type" => "integer", "unsigned" => true, "default" => NULL, 'index' => true],
            "record_id" => ["type" => "integer", "unsigned" => true, "default" => NULL, 'index' => true],
            "entity" => ["type" => "string", "length" => 50],
            "payload" => ["type" => "text"],
            "name" => ["type" => "string", "length" => 50],
            "before" => ["type" => "string", "length" => 50],
            "after" => ["type" => "string", "length" => 50],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, 'App\User', 'user_id')
        ];
    }
    
    public function transform(History $entity)
    {
        return [
            "id" => (integer) $entity->id ?: null,
            "user_id" => $entity->user && $entity->user->id?$entity->user->id:"?",
            "user_name" => $entity->user && $entity->user->id?$entity->user->id:"Usuario desconocido",
            "record_id" => (integer) $entity->record_id ?: null,
            "entity" => (string) $entity->entity ?: "",
            "name" => (string) $entity->name ?: "",
            "before" => (string) $entity->before ?: "",
            "after" => (string) $entity->after ?: "",
            "created" => (string) $entity->created->format('U') ?: ""
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "entity" => null
        ]);
    }
}
