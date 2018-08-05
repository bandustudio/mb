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

class Product extends \Spot\Entity
{
    protected static $table = "products";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "model_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "position_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],            
            "type_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "title" => ["type" => "string", "length" => 250],
            "title_slug" => ["type" => "string", "length" => 250],
            "picshare_url" => ["type" => "string", "length" => 255],
            "pic1_url" => ["type" => "string", "length" => 255],
            "pic2_url" => ["type" => "string", "length" => 255],
            "pic3_url" => ["type" => "string", "length" => 255],
            "pic4_url" => ["type" => "string", "length" => 255],
            "pic5_url" => ["type" => "string", "length" => 255],
            "pic6_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "intro" => ["type" => "text"],
            "content" => ["type" => "text"],
            "fecha" => ["type" => "string", "length" => 50],
            "model" => ["type" => "string", "length" => 50],
            "version" => ["type" => "string", "length" => 50],
            "status" => ["type" => "string", "length" => 50],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "featured" => ["type" => "boolean", "default" => false, "value" => false],
            "enabled" => ["type" => "boolean", "default" => true, "value" => true],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'type' => $mapper->belongsTo($entity, 'App\ThemeType', 'type_id'),
            'model' => $mapper->belongsTo($entity, 'App\ProductModel', 'model_id'),
            'position' => $mapper->belongsTo($entity, 'App\ThemePosition', 'position_id')
        ];
    }
    
    public function transform(Product $entity)
    {

        $slick = [];
        for($i=2;$i<7;$i++){
            if(!empty($entity->{'pic' . $i . '_url'})){
                $slick = $entity->{'pic' . $i . '_url'};
            }
        }

        return [
            "id" => (integer) $entity->id ?: null,
            "title" => (string) $entity->title ?: "",
            "slug" => (string) $entity->title_slug ?: "",
            "intro" => (string) $entity->intro ?: "",
            "content" => (string) $entity->content ?: "",
            "picshare_url" => (string) $entity->picshare_url ?: "",
            "background_url" => (string) $entity->background_url ?: "",
            "picture" => (string) $entity->pic1_url ?: "",
            "slick" => (array) $slick,
            "status" => (string) $entity->status ?: "",
            'pic' => \subpic('200x140',$entity->pic1_url),
            //"created" => (string) $entity->created->format('U') ?: "",
            "model" => [
                "id" => (integer) $entity->model ? $entity->model_id : null,
                "title" => (string) $entity->model ? $entity->model->title : null
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
