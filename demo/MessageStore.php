<?php

use Lit\RedisExt\Mapper\GroupMessageMapper;
use Lit\RedisExt\Mapper\SingleMessageMapper;

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");
\Lit\RedisExt\MessageStore::init($redisHandler);

$message = new \Lit\RedisExt\Mapper\SingleMessageMapper();
$message->topic = "test";
$message->uniqId = uniqid();
$message->uniqId = 111;
$message->body = "测试Message";
$message->sendTime = date("Y-m-d H:i:s", time());
$message->duplicateSecond = 60;

$dingSender = new \Lit\RedisExt\Mapper\DingSenderMapper();
\Lit\RedisExt\MessageStore::single()->setMessage($message)->send();


$message = new \Lit\RedisExt\Mapper\GroupMessageMapper();
$message->topic = "test";
$message->uniqId = uniqid();
$message->uniqId = 222;
$message->body = "测试Message";
$message->sendTime = date("Y-m-d H:i:s", time());
$message->duplicateSecond = 60;
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message);
$messageSender->send();
var_dump($messageSender->getErrorCode(), $messageSender->getErrorMessage());

\Lit\RedisExt\MessageStore::run(function (array $message) {
    var_dump(111, $message);
}, function (array $message) {
    var_dump(222, $message);
});

