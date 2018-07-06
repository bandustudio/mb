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

class User extends \Spot\Entity
{
    protected static $table = "users";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "role_id" => ["type" => "integer", "unsigned" => true, 'index' => true, 'value' => 1],
            "region_id" => ["type" => "integer", "unsigned" => true, 'index' => true],
            "city_id" => ["type" => "integer", "unsigned" => true, 'index' => true],
            "email" => ["type" => "string", "length" => 50, "unique" => true],
            "username" => ["type" => "string", "length" => 100, "unique" => true],
            "first_name" => ["type" => "string", "length" => 32],
            "last_name" => ["type" => "string", "length" => 32],
            "password" => ["type" => "string", "length" => 255],
            "phone" => ["type" => "string", "length" => 255],
            "cc_number" => ["type" => "string", "length" => 255],
            "cc_exp_date" => ["type" => "string", "length" => 255],
            "cc_entity" => ["type" => "string", "length" => 255],
            "cc_name" => ["type" => "string", "length" => 255],
            "phone" => ["type" => "string", "length" => 255],
            "address" => ["type" => "string", "length" => 255],
            "locality" => ["type" => "string", "length" => 50],
            "administrative_area_level_1" => ["type" => "string", "length" => 50],
            "administrative_area_level_2" => ["type" => "string", "length" => 50],
            "formatted_address" => ["type" => "string", "length" => 250],
            "country" => ["type" => "string", "length" => 50],
            "vicinity" => ["type" => "string", "length" => 50],
            "map_icon" => ["type" => "string", "length" => 250],
            "map_url" => ["type" => "string", "length" => 250],
            "utc" => ["type" => "string", "length" => 20],
            "lat" => ["type" => "string", "length" => 50],
            "lng" => ["type" => "string", "length" => 50],                                    
            "dnicuit" => ["type" => "string", "length" => 50],                                    
            "password_token" => ["type" => "string", "length" => 255],
            "token" => ["type" => "text"],
            "facebook_id" => ["type" => "decimal", "precision" => "21", "unique" => true],
            "google_id" => ["type" => "decimal", "precision" => "21", "unique" => true],
            "picture" => ["type" => "string", "length" => "255","value" => "https://static.sectorseguro.com/img/placeholder.png", "default" => "https://static.sectorseguro.com/img/placeholder.png"],
            "background" => ["type" => "string", "length" => "255"],
            "newsletter" => ["type" => "boolean", "value" => false],
            "terms" => ["type" => "boolean", "value" => false],
            "as_ranking" => ["type" => "integer","value" => 99, "default" => 99],
            "as_gestion" => ["type" => "integer","length" => 2, "default" => null],
            "validated" => ["type" => "boolean", "value" => false],
            "last_activity" => ["type" => "integer", "length" => 15],            
            "online" => ["type" => "boolean", "value" => false],
            "anonymous" => ["type" => "boolean", "value" => false],
            "enabled" => ["type" => "boolean", "value" => true],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            //'lead' => $mapper->hasOne($entity, 'App\Lead', 'gestor_id'),
            'role' => $mapper->belongsTo($entity, 'App\UserRole', 'role_id'),
            'region' => $mapper->belongsTo($entity, 'App\Region', 'region_id'),
            'city' => $mapper->belongsTo($entity, 'App\City', 'city_id'),
            'uploads' => $mapper->hasMany($entity, 'App\Upload', 'user_id')->order(['created' => 'DESC'])
        ];
    }

    public function transform(User $entity)
    {
        $member_since = $entity->created;
        $member_since_date = $member_since->format('U');

        if(strlen($entity->first_name) OR strlen($entity->last_name)){
            $title = implode(" ",array_values([$entity->first_name,$entity->last_name]));
        } else {
            $title = $entity->email;
        }

        $uploads = [];
        foreach($entity->uploads as $item){
            $uploads[] = [
                'id' => $item->id,
                'file_url' => $item->file_url,
                'filesize' => $item->filesize,
                'updated' => $item->updated->format('U')
            ];
        }

        return [
            "id" => (integer) $entity->id ?: null,
            "role_id" => (integer) $entity->role_id ?: null,
            "region_id" => (integer) $entity->region_id ?: null,
            "city_id" => (integer) $entity->city_id ?: null,
            "email" => (string) $entity->email ?: null,
            "phone" => (string) $entity->phone ?: null,
            "email_encoded" => (string) $entity->email ? Base62::encode($entity->email): null,
            "first_name" => (string) $entity->first_name ?: "",
            "last_name" => (string) $entity->last_name ?: "",
            "full_name" => (string) $entity->first_name . ' ' . $entity->last_name,
            "username" => (string) $entity->username ?: "",
            "dnicuit" => (string) $entity->dnicuit ?: "",
            "title" => $title,
            "picture" => (string) $entity->picture ?: "",
            "uploads" => (array) $uploads ?: "",
            "background" => (string) $entity->background ?: "",
            "validated" => !!$entity->validated,
            "anonymous" => !!$entity->anonymous,
            "cc_number" => (string) $entity->cc_number ?: "",
            "cc_exp_date" => (string) $entity->cc_exp_date ?: "",
            "cc_entity" => (string) $entity->cc_entity ?: "",
            "cc_name" => (string) $entity->cc_name ?: "",
            "address" => (string) $entity->address ?: "",
            "lat" => (string) $entity->lat ?: "",
            "lng" => (string) $entity->lng ?: "",
            "locality" => (string) $entity->locality ?: "",
            "administrative_area_level_1" => (string) $entity->administrative_area_level_1 ?: "",
            "administrative_area_level_2" => (string) $entity->administrative_area_level_2 ?: "",
            "country" => (string) $entity->country ?: "",
            "vicinity" => (string) $entity->vicinity ?: "",
            "map_icon" => (string) $entity->map_icon ?: "",
            "map_url" => (string) $entity->map_url ?: "",
            "formatted_address" => (string) $entity->formatted_address ?: "",
            "utc" => (string) $entity->utc ?: "",
            "member_since" => \human_timespan($member_since_date),
            "token" => \set_token($entity),
            "owned" => \get_owned($entity),
            "preferences" => \get_preferences($entity)
        ];
    }

    public function timestamp()
    {
        return $this->updated_at->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "username" => null,
            "password" => null,
            "enabled" => null
        ]);
    }
}
