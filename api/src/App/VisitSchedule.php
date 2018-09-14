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

class VisitSchedule extends \Spot\Entity
{
    protected static $table = "visits_schedule";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            //"user_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "code" => ["type" => "string", "length" => 255],
            "email" => ["type" => "string", "length" => 50],
            "phone" => ["type" => "string", "length" => 50],
            "full_name" => ["type" => "string", "length" => 100],
            "first_name" => ["type" => "string", "length" => 50],
            "last_name" => ["type" => "string", "length" => 50],
            "reason" => ["type" => "string", "length" => 20],
            "comment" => ["type" => "text", "charset" => "utf8mb4_general_ci"],
            "scheduled"   => ["type" => "datetime", "value" => null],
            "enabled" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Turno $mail)
    {
        return [
            "id" => (integer) $mail->id ?: null,
            "first_name" => (string) $mail->first_name ?: "",
            "last_name" => (string) $mail->last_name ?: "",
            "comment" => (string) $mail->comment ?: "",
            "code" => (string) $mail->code ?: ""
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "title" => null,
            "code" => null,
            "enabled" => null
        ]);
    }
}
