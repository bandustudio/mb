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

class Post extends \Spot\Entity
{
    protected static $table = "posts";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "user_id" => ["type" => "integer", "unsigned" => true, "default" => NULL, 'index' => true],
            "picture_url" => ["type" => "string", "length" => 255],
            "background_url" => ["type" => "string", "length" => 255],
            "title" => ["type" => "string", "length" => 250],
            "content" => ["type" => "text"],
            "fecha" => ["type" => "string", "length" => 50],
            "model" => ["type" => "string", "length" => 50],
            "version" => ["type" => "string", "length" => 50],
            "status" => ["type" => "string", "length" => 50],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "value" => false],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, 'App\User', 'user_id'),
            //'region' => $mapper->belongsTo($entity, 'App\Region', 'region_id'),
            //'city' => $mapper->belongsTo($entity, 'App\City', 'city_id'),
            //'brand' => $mapper->belongsTo($entity, 'App\Brand', 'brand_id'),
            //'model' => $mapper->belongsTo($entity, 'App\Model', 'model_id'),
            //'version' => $mapper->belongsTo($entity, 'App\Version', 'version_id'),
            'uploads' => $mapper->hasMany($entity, 'App\Upload', 'lead_id')->order(['created' => 'DESC'])
        ];
    }
    
    public function transform(Post $entity)
    {

        $uploads = [];
        foreach($entity->uploads as $item){
            $uploads[] = [
                'id' => $item->id,
                'file_url' => $item->file_url,
                'filesize' => $plan->filesize,
                'updated' => $item->updated->format('U')
            ];
        }

        return [
            "id" => (integer) $entity->id ?: null,
            "picture" => (string) $entity->picture_url ?: "",
            "background" => (string) $entity->background_url ?: "",
            "uploads" => (array) $uploads ?: [],
            "brand" => (string) $entity->model ?: "",
            "model" => (string) $entity->brand ?: "",
            "status" => (string) $entity->status ?: "",
            "version" => (string) $entity->version ?: "",
            "created" => (string) $entity->created->format('U') ?: "",
            "user" => [
                'id' => (integer) $entity->user->id ?: null,
                "username" => (string) $entity->user->username ?: "",
                "first_name" => (string) $entity->user->first_name ?: "",
                "last_name" => (string) $entity->user->last_name ?: "",
                "picture" => (string) $entity->user->picture ?: ""
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
