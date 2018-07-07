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

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use Spot\EntityInterface as Entity;
use Spot\MapperInterface as Mapper;
use Spot\EventEmitter;
use Tuupola\Base62;
use Ramsey\Uuid\Uuid;
use Psr\Log\LogLevel;
use Spot\Locator;
use Doctrine\DBAL\Query\QueryBuilder;
use Intervention\Image\ImageManager;
use App\Model;

class Cron extends \Spot\Entity
{
    protected static $table = "cron";

    public static function fields()
    {
        return [
            "id" => ["type" => "integer", "unsigned" => true, "primary" => true, "autoincrement" => true],
            "report" => ["type" => "text"],
            "success" => ["type" => "boolean", "value" => false],
            "created"   => ["type" => "datetime", "value" => new \DateTime()],
            "updated"   => ["type" => "datetime", "value" => new \DateTime()]
        ];
    }

    public function transform(Cron $cron)
    {
        return [
            "id" => (integer) $cron->id ?: null,
            "report" => (string) $cron->report ?: "",
            "success" => !!$cron->success
        ];
    }

    public function timestamp()
    {
        return $this->updated->getTimestamp();
    }

    public function clear()
    {
        $this->data([
            "report" => null,
            "success" => null
        ]);
    }

    public function seed(){

        global $container;


        $location = __DIR__ . '/../../bin/mercedesbenz.sql';
        //load file
        $commands = file_get_contents($location);

        //delete comments
        $lines = explode("\n",$commands);
        $commands = '';
        foreach($lines as $line){
            $line = trim($line);
            if( $line && strlen(trim($line)) > 10 && substr($line, 0, strlen('--')) != '--' ) {
                $commands .= $line . "\n";
            }
        }

        //convert to array
        $commands = explode(";", $commands);

        $link = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));

        //run commands
        $total = $success = 0;
        foreach($commands as $command){
            if(trim($command)){
                //$success += ($container["spot"]->mapper("App\Model")->query($command)==false ? 0 : 1);
                $success += (\mysqli_query($link,$command)==false ? 0 : 1);
                $total += 1;
            }
        }

        mysqli_close($link);
        print "Total de tareas exitosas: " . $success . " de " . $total . PHP_EOL;
    }

    public function mytask(){    

        global $container;

        $userchanged = 0;
        $users = $container["spot"]->mapper("App\User")
            ->all()
            ->order(['id' => "ASC"]);

        $users = $container["spot"]->mapper("App\User")->query("
            SELECT * 
            FROM users 
            WHERE picture LIKE '%akamaihd.net%'");

        foreach($users as $user){
            $parts = explode('_',$user->picture);
            $facebook_id = $parts[1];

            if(!$user->facebook_id){
                $user->data(['facebook_id' => $facebook_id]);
                $container["spot"]->mapper("App\User")->save($user);
                $userchanged++;
            }
            
        }

        print "Total de usuarios actualizados: " . $userchanged . PHP_EOL;
    }
}