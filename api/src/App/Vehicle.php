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

class Vehicle extends \Spot\Entity
{
    protected static $table = "vehicles";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "model_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "position_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],            
            "type_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "picshare_url" => ["type" => "string", "length" => 255],
            "background_url" => ["type" => "string", "length" => 255],
            "pic1_url" => ["type" => "string", "length" => 255],
            "pic2_url" => ["type" => "string", "length" => 255],
            "pic3_url" => ["type" => "string", "length" => 255],
            "pic4_url" => ["type" => "string", "length" => 255],
            "pic5_url" => ["type" => "string", "length" => 255],
            "pic6_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "title" => ["type" => "string", "length" => 250],
            "content" => ["type" => "text"],
            "fecha" => ["type" => "string", "length" => 50],
            "model" => ["type" => "string", "length" => 50],
            "version" => ["type" => "string", "length" => 50],
            "status" => ["type" => "string", "length" => 50],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "value" => true],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'type' => $mapper->belongsTo($entity, 'App\ThemeType', 'type_id'),
            'model' => $mapper->belongsTo($entity, 'App\VehicleModel', 'model_id'),
            'position' => $mapper->belongsTo($entity, 'App\ThemePosition', 'position_id')
        ];
    }
    
    public function transform(Vehicle $entity)
    {

        return [
            "id" => (integer) $entity->id ?: null,
            "title" => (string) $entity->title ?: "",
            "content" => (string) $entity->content ?: "",
            "picshare_url" => (string) $entity->picshare_url ?: "",
            "background_url" => (string) $entity->background_url ?: "",
            "pic1_url" => (string) $entity->pic1_url ?: "",
            "pic2_url" => (string) $entity->pic2_url ?: "",
            "pic3_url" => (string) $entity->pic3_url ?: "",
            "pic4_url" => (string) $entity->pic4_url ?: "",
            "pic5_url" => (string) $entity->pic5_url ?: "",
            "pic6_url" => (string) $entity->pic6_url ?: "",
            "status" => (string) $entity->status ?: "",
            //"created" => (string) $entity->created->format('U') ?: "",
            "model" => [
                "id" => (integer) $entity->model_id ?: null,
                "title" => (string) $entity->model->title ?: null
            ]
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "fullname" => null
        ]);
    }
}
