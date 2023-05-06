<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("utils-redis");

//初始化 Redis String
\Lit\RedisExt\CacheString::init($redisHandler);

$id1 = 1;
$id2 = 2;
$key = "tmpKey:" . $id1 . ":" . $id2;
$version = "1.0.3";

$data = \Lit\RedisExt\CacheString::getOrSet($key, function ($id1, $id2) {
    return $id1 . ":" . $id2 . ":" . uniqid();
}, [$id1, $id2], $version, 3600);

var_dump($data);
