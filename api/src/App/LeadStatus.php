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

class Plan extends \Spot\Entity
{
    protected static $table = "lead_statuses";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "lead_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true],
            "name" => ["type" => "string", "length" => 100],
            "text" => ["type" => "text"],
            "price" => ["type" => "decimal", "precision" => 10, "scale" => 0, "value" => 0, "default" => 0, "notnull" => true],
            "currency" => ["type" => "string", "length" => 3, "value" => "ARS"],
            "enabled" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'lead' => $mapper->belongsTo($entity, 'App\Lead', 'lead_id')
        ];
    }

    public function transform(Plan $entity)
    {
        return [
            "id" => (integer) $entity->id ?: null,
            "lead_id" => (integer) $entity->lead_id ?: null,
            "lead_code" => (string) $entity->lead->code ?: null,
            "name" => (string) $entity->name ?: "",
            "text" => (string) $entity->text ?: "",
            "price" => (string) $entity->price ?: "",
            "currency" => (string) $entity->currency ?: "",
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
            "lead_id" => null,
            "plan_id" => null
        ]);
    }
}
