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

class Service extends \Spot\Entity
{
    protected static $table = "services";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "title" => ["type" => "string", "length" => 250],
            "title_slug" => ["type" => "string", "length" => 250],
            "intro" => ["type" => "text"],
            "pic_off_url" => ["type" => "string", "length" => 255],
            "pic_on_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "content_html" => ["type" => "text"],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "default" => true, "value" => true],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Service $entity)
    {

        return [
            "id" => (integer) $entity->id ?: null,
            "title" => (string) $entity->title ?: "",
            "intro" => (string) $entity->intro ?: "",
            "slug" => (string) $entity->title_slug ?: "",
            "lat" => (string) $entity->lat ?: "",
            "lng" => (string) $entity->lng ?: "",
            "formatted_address" => (string) $entity->formatted_address ?: "",
            "content" => (string) $entity->content_html ?: "",
            "pic_off" => (string) $entity->pic_off_url ?: "",
            "pic_on" => (string) $entity->pic_on_url ?: "",
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
