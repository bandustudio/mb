<?php

/*
 * This file is part of the Slim API skeleton packagesdfsdf
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

class Test extends \Spot\Entity
{
    protected static $table = "test";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "address_places" => ["type" => "string", "length" => 250],
            "locality" => ["type" => "string", "length" => 50],
            "administrative_area_level_1" => ["type" => "string", "length" => 50],
            "administrative_area_level_2" => ["type" => "string", "length" => 50],
            "formatted_address" => ["type" => "string", "length" => 250],
            "country" => ["type" => "string", "length" => 50],
            "vicinity" => ["type" => "string", "length" => 50],
            "mapicon" => ["type" => "string", "length" => 250],
            "mapurl" => ["type" => "string", "length" => 250],
            "address" => ["type" => "string", "length" => 250],
            "utc" => ["type" => "string", "length" => 20],
            "lat" => ["type" => "string", "length" => 50],
            "lng" => ["type" => "string", "length" => 50],                                    
            "pic1_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "content_html" => ["type" => "text"],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Test $entity)
    {

        return [
            "id" => (integer) $entity->id ?: null,
            "title" => (string) $entity->title ?: "",
            "intro" => (string) $entity->intro ?: "",
            "lat" => (string) $entity->lat ?: "",
            "lng" => (string) $entity->lng ?: "",
            "formatted_address" => (string) $entity->formatted_address ?: "",
            "button_value" => (string) $entity->button_value ?: "",
            "button_link" => (string) $entity->button_link ?: "",
            "slug" => (string) $entity->title_slug ?: "",
            "content" => (string) $entity->content_html ?: "",
            "picshare_url" => (string) $entity->picshare_url ?: "",
            "background_url" => (string) $entity->background_url ?: "",
            "picture" => (string) $entity->pic1_url ?: "",
            "pic_options" => $pic_options,
            "sizes" => (array) $sizes,
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
