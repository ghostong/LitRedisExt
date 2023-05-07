<?php

use Lit\RedisExt\Structs\CacheStringKey;

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("utils-redis");

//初始化 Redis String
\Lit\RedisExt\CacheString::init($redisHandler);
$version = "1.0.0";

//获取缓存数据
var_dump(\Lit\RedisExt\CacheString::get("tmpKey:0", $version));

//写入缓存数据
var_dump(\Lit\RedisExt\CacheString::set("tmpKey:0", ["tmp:1:" . uniqid(), "tmp:2:" . uniqid()], $version, 30));

//获取缓存数据,不存在时则通过回调函数初始化
$keyObject = new CacheStringKey("tmpKey:0:0", [1, 1]);
$data = \Lit\RedisExt\CacheString::getOrSet($keyObject, function ($id1, $id2) {
    return $id1 . ":" . $id2 . ":" . uniqid();
}, $version, 30);


//批量获取缓存数据
var_dump(\Lit\RedisExt\CacheString::mGet(["tmpKey:1", "tmpKey:2", "tmpKey:3"], $version));

//批量写入缓存数据
$cacheData = ["tmpKey:1" => "tmp:1:" . uniqid(), "tmpKey:2" => "tmp:2:" . uniqid(), "tmpKey:3" => "tmp:3:" . uniqid()];
var_dump(\Lit\RedisExt\CacheString::mSet($cacheData, $version, 30));

//批量获取缓存数据,不存在时则通过回调函数初始化
$keyObjects = [
    new CacheStringKey("tmpKey:1:1", [1, 1]),
    new CacheStringKey("tmpKey:2:2", [2, 2]),
    new CacheStringKey("tmpKey:3:3", [3, 3])
];
$data = \Lit\RedisExt\CacheString::mGetOrSet($keyObjects, function ($id1, $id2) {
    return $id1 . ":" . $id2 . ":" . uniqid();
}, $version, 30);
var_dump($data);