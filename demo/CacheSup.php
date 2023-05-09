<?php

use Lit\RedisExt\Structs\CacheSupGetKey;

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("utils-redis");

//初始化 Redis String
\Lit\RedisExt\CacheSup::init($redisHandler);

$keyObj = new \Lit\RedisExt\Structs\CacheSupRangeKey();
$keyObj->key = "list:a:2";
$keyObj->cursor = 21;
$keyObj->limit = 1;
$tmp = \Lit\RedisExt\CacheSup::zRangeOrAdd($keyObj, function () {
    return [["name" => "haha", "age" => 20], ["name" => "hehe", "age" => 30], ["name" => "hello", "age" => 40], ["name" => "world", "age" => 50]];
}, function ($value) {
    return $value["name"];
}, function ($value) {
    return $value["age"];
}, 1);

var_dump($tmp);

//----------------------//
exit;
$version = "1.0.0";

//获取缓存数据
var_dump(\Lit\RedisExt\CacheSup::get("tmpKey:0", $version));

//写入缓存数据
var_dump(\Lit\RedisExt\CacheSup::set("tmpKey:0", ["tmp:1:" . uniqid(), "tmp:2:" . uniqid()], $version, 30));

//获取缓存数据,不存在时则通过回调函数初始化
$keyObject = new CacheSupGetKey("tmpKey:0:0", [1, 1]);
$data = \Lit\RedisExt\CacheSup::getOrSet($keyObject, function ($id1, $id2) {
    return $id1 . ":" . $id2 . ":" . uniqid();
}, $version, 30);


//批量获取缓存数据
var_dump(\Lit\RedisExt\CacheSup::mGet(["tmpKey:1", "tmpKey:2", "tmpKey:3"], $version));

//批量写入缓存数据
$cacheData = ["tmpKey:1" => "tmp:1:" . uniqid(), "tmpKey:2" => "tmp:2:" . uniqid(), "tmpKey:3" => "tmp:3:" . uniqid()];
var_dump(\Lit\RedisExt\CacheSup::mSet($cacheData, $version, 30));

//批量获取缓存数据,不存在时则通过回调函数初始化
$keyObjects = [
    new CacheSupGetKey("tmpKey:1:1", [1, 1]),
    new CacheSupGetKey("tmpKey:2:2", [2, 2]),
    new CacheSupGetKey("tmpKey:3:3", [3, 3])
];
$data = \Lit\RedisExt\CacheSup::mGetOrSet($keyObjects, function ($id1, $id2) {
    return $id1 . ":" . $id2 . ":" . uniqid();
}, $version, 30);
var_dump($data);