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

class UserMessage extends \Spot\Entity
{
    protected static $table = "users_messages";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "message_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true, 'notnull' => true],
            "user_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true, 'notnull' => true],
            "recipient_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true, 'notnull' => true],
            "lead_id" => ["type" => "integer", "unsigned" => true, "value" => 0, 'index' => true, 'notnull' => true],
            "tag" => ["type" => "string", "length" => 20],
            "hasread" => ["type" => "boolean", 'value' => false, 'notnull' => true],
            "dateread" => ["type" => "datetime", "value" => new \DateTime()],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'message' => $mapper->belongsTo($entity, 'App\Message', 'message_id'),
            'user' => $mapper->belongsTo($entity, 'App\User', 'user_id'),
            'recipient' => $mapper->belongsTo($entity, 'App\User', 'recipient_id'),
            'lead' => $mapper->belongsTo($entity, 'App\Lead', 'lead_id')
        ];
    }

    public function transform(UserMessage $um)
    {
        $usertitle = "";

        if(strlen(trim($um->user->first_name)) OR strlen(($um->user->last_name))){
            $usertitle = implode(" ",array_values([$um->user->first_name,$um->user->last_name]));
        } else {
            $usertitle = $um->user->email;
        }
        $recipienttitle = "";

        if(strlen(trim($um->recipient->first_name)) OR strlen(($um->recipient->last_name))){
            $recipienttitle = implode(" ",array_values([$um->recipient->first_name,$um->recipient->last_name]));
        } else {
            $recipienttitle = $um->recipient->email;
        }     

        return [
            "id" => (integer) $um->id ?: null,
            "user_id" => (integer) $um->user_id ?: "",
            "lead_id" => (integer) $um->lead_id ?: "",
            "message_id" => (integer) $um->message_id ?: "",
            "recipient_id" => (integer) $um->recipient_id ?: "",
            "timespan" => \human_timespan_short($um->created->format('U')),
            "created" => (string) $um->created->format('U') ?: "",
            "message" => [
                "content" => (string) $um->message->content ? : null,
                "author" => ((string) $um->message->user->first_name ? : "") . ' ' . ((string) $um->message->user->last_name ? : "")
            ],
            "user" => [
                "id" => (integer) $um->user_id ? : null,
                "title" => $usertitle,
                "email" => (string) $um->user->email ? : null,
                "picture" => (string) $um->user->picture ? : null
            ],
            "recipient" => [
                "id" => (integer) $um->recipient_id ? : null,
                "title" => $recipienttitle,
                "email" => (string) $um->recipient->email ? : null,
                "picture" => (string) $um->recipient->picture ? : null
            ],
           "lead" => [
                "id" => (INTEGER) $um->lead->id ? : null,
                "code" => (string) $um->lead->code ? : null,
                "title" => ((string) $um->lead->brand ? : "") . ' ' . ((string) $um->lead->model ? : ""),
                "mt_year" => (integer) $um->lead->mt_year ? : null,
                "links"        => [
                    "self" => "/" . $um->lead->code . '---' . $um->lead->title
                ]                
            ],
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "user_id" => null,
            "recipient_id" => null,
            "message_id" => null
        ]);
    }
}
