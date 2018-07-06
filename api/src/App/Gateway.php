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

class Gateway extends \Spot\Entity
{
    protected static $table = "gateway";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "user_id" => ["type" => "integer", "unsigned" => true, "default" => NULL, 'index' => true],
            "cc_number" => ["type" => "string", "length" => 50],
            "cc_exp_date" => ["type" => "string", "length" => 50],
            "cc_entity" => ["type" => "string", "length" => 50],
            "cc_name" => ["type" => "string", "length" => 50],
            "fav" => ["type" => "boolean", "value" => false, "notnull" => true],
            "enabled" => ["type" => "boolean", "value" => false],            
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
    
    public function transform(Gateway $entity)
    {

        $prices = [];
        foreach($entity->prices as $price){
            $prices[] = [
                'id' => $price->id,
                'name' => $price->name,
                'price' => $price->price,
                'text' => $price->text,
                'currency' => $price->currency,
                'updated' => $price->updated->format('U')
            ];
        }

        return [
            "id" => (integer) $entity->id ?: null,
            "full_name" => (string) $entity->full_name ?: "",
            "first_name" => (string) $entity->first_name ?: "",
            "last_name" => (string) $entity->last_name ?: "",
            "address" => (string) $entity->address ?: "",
            "administrative_area_level_1" => (string) $entity->administrative_area_level_1 ?: "",
            "administrative_area_level_2" => (string) $entity->administrative_area_level_2 ?: "",
            "formatted_address" => (string) $entity->formatted_address ?: "",
            "vicinity" => (string) $entity->vicinity ?: "",
            "country" => (string) $entity->country ?: "",
            "prices" => (array) $prices ?: [],
            "email" => (string) $entity->email ?: "",
            //"city" => (string) $entity->city ?: "",
            "brand" => (string) $entity->brand ?: "",
            "use" => (string) $entity->use ?: "",
            "gas" => !!$vehicle->gas,
            "model" => (string) $entity->model ?: "",
            "product_since" => (string) $entity->product_since ? $entity->product_since->format('U') : "",
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
            "fullname" => null
        ]);
    }
}
