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

class Payment extends \Spot\Entity
{
    protected static $table = "pagos";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "lead_id" => ["type" => "integer", "unsigned" => true, "default" => 0, 'index' => true],
            "quantity" => ["type" => "integer", "length" => 3, "value" => 1, "default" => 1],
            "currency" => ["type" => "string", "length" => 3],
            "first_name" => ["type" => "string", "length" => 50],
            "last_name" => ["type" => "string", "length" => 50],
            "status" => ["type" => "string", "length" => 20],
            "origin" => ["type" => "string", "length" => 20],
            "amount" => ["type" => "decimal", "precision" => 15, "scale" => 2, "value" => 0, "default" => 0, "notnull" => true],
            "tel" => ["type" => "string", "length" => 20],
            "email" => ["type" => "string", "length" => 50],
            "date_generated"   => ["type" => "datetime", "value" => new \DateTime()],
            "date_complete"   => ["type" => "datetime", "value" => new \DateTime()],
            "complete" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'lead' => $mapper->belongsTo($entity, 'App\Lead', 'lead_id')
        ];
    }

    public function transform(Payment $payment)
    {
        return [
            "id" => (integer) $payment->id ?: null,
            "first_name" => (string) $payment->first_name ?: "",
            "last_name" => (string) $payment->last_name ?: "",
            "status" => (string) $payment->status ?: "",
            "amount" => (float) $payment->amount ?: "",
            "currency" => (string) $payment->currency ?: "",
            "tel" => (string) $payment->tel ?: "",
            "email" => (string) $payment->email ?: "",
            "beverage" => (string) $payment->beverage ?: "",
            "date_payment" => (string) $payment->date_payment ?: "",
            "created" => (string) $payment->created->format('Y-m-d H:i') ?: "",
            "complete" => !!$lead->complete,
            "product" => (object) $payment->product ?: null,
            "lead" => [
                "id" => (integer) $payment->lead->id ? : null,
                "title" => ((string) $payment->lead->brand->title ? : "") . ' ' . ((string) $payment->lead->model->title ? : ""),
                "price" => (string) $payment->lead->price ? : null,
                "photos" => $photos,
                "user" => [
                    "id" => (integer) $payment->lead->user_id ? : null,
                    "title" => ((string) $payment->lead->user->first_name ? : "") . ' ' . ((string) $payment->lead->user->last_name ? : ""),
                    "username" => (string) $payment->lead->user->username ? : null,
                    "email" => (string) $payment->lead->user->email ? : null,
                    "picture" => (string) $payment->lead->user->picture ? : null,
                    "background" => (string) $payment->lead->user->background ? : null
                ]
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
            "fullname" => null,
            "tel" => null,
            "email" => null
        ]);
    }
}
