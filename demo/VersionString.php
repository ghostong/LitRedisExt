<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");
\Lit\RedisExt\VersionString::init($redisHandler);

$key = "tmpKey:1";
$version = "1.0.0";
\Lit\RedisExt\VersionString::getOrSet($key, function ($value) {
    var_dump($value);
}, $version);