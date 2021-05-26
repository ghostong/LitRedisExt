<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 固定集合
 */

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");

//初始化固定集合
Lit\RedisExt\CappedCollections::init($redisHandler);

//固定集合写入数据
var_dump(Lit\RedisExt\CappedCollections::set("cappedKey", uniqid(), 20));

//获取固定集合中的数据量
var_dump(Lit\RedisExt\CappedCollections::size("cappedKey"));

//获取固定集合数据
var_dump(Lit\RedisExt\CappedCollections::get("cappedKey", 15, 5));

//销毁固定集合
var_dump(Lit\RedisExt\CappedCollections::destroy("cappedKey"));