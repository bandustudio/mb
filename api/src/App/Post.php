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
            "section_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "position_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "title" => ["type" => "string", "length" => 250],
            "title_slug" => ["type" => "string", "length" => 250],
            "intro" => ["type" => "text"],
            "button_link" => ["type" => "string", "length" => 250],
            "pic1_url" => ["type" => "string", "length" => 255],
            "pic2_url" => ["type" => "string", "length" => 255],
            "pic3_url" => ["type" => "string", "length" => 255],
            "pic4_url" => ["type" => "string", "length" => 255],
            "pic5_url" => ["type" => "string", "length" => 255],
            "pic6_url" => ["type" => "string", "length" => 255],
            "picshare_url" => ["type" => "string", "length" => 255],
            "background_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "content_text" => ["type" => "text"],
            "from_datetime" => ["type" => "datetime", "value" => new \DateTime()],
            "to_datetime" => ["type" => "datetime", "value" => new \DateTime()],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "default" => true, "value" => true],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'section' => $mapper->belongsTo($entity, 'App\ThemeSection', 'section_id'),
            'position' => $mapper->belongsTo($entity, 'App\ThemePosition', 'position_id')
        ];
    }
    
    public function transform(Post $entity)
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
            "content" => (string) $entity->content_text ?: "",
            "picshare_url" => (string) $entity->picshare_url ?: "",
            "background_url" => (string) $entity->background_url ?: "",
            "picture" => (string) $entity->pic1_url ?: "",
            "slick" => (array) $slick,
            "status" => (string) $entity->status ?: ""
            //"created" => (string) $entity->created->format('U') ?: "",
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
