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

class Dealer extends \Spot\Entity
{
    protected static $table = "dealers";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "title" => ["type" => "string", "length" => 250],
            "title_slug" => ["type" => "string", "length" => 250],
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
            "intro" => ["type" => "text"],
            "pic1_url" => ["type" => "string", "length" => 255],
            "pic2_url" => ["type" => "string", "length" => 255],
            "pic3_url" => ["type" => "string", "length" => 255],
            "youtube" => ["type" => "string", "length" => 50],
            "content_html" => ["type" => "text"],
            "deleted" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "default" => true, "value" => true],
            "created" => ["type" => "datetime", "value" => new \DateTime()],
            "updated" => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Dealer $entity)
    {

        $slick = [];
        for($i=2;$i<4;$i++){
            if(!empty($entity->{'pic' . $i . '_url'})){
                $slick = $entity->{'pic' . $i . '_url'};
            }
        }

        $resolutions = explode(',',getenv('UPLOADS_RESOLUTIONS'));
        $sizes = [];

        foreach($resolutions as $res){
            $parts = explode('/',$entity->pic1_url);
            $name = $parts2[count($parts2)-1];
            $parts[count($parts)-1] = $res.$parts[count($parts)-1];
            $sizes[$res]=implode('/',$parts);
        }

        return [
            "id" => (integer) $entity->id ?: null,
            "title" => (string) $entity->title ?: "",
            "intro" => (string) $entity->intro ?: "",
            "slug" => (string) $entity->title_slug ?: "",
            "lat" => (string) $entity->lat ?: "",
            "lng" => (string) $entity->lng ?: "",
            "formatted_address" => (string) $entity->formatted_address ?: "",
            "content" => (string) $entity->content_html ?: "",
            "picture" => (string) $entity->pic1_url ?: "",
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
