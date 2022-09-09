<?php
include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 独占锁
 */
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");

//初始化
\Lit\RedisExt\XLocks::init($redisHandler);

//获取锁
var_dump(\Lit\RedisExt\XLocks::lock("testa", 20));

//获取锁剩余生命周期
var_dump(\Lit\RedisExt\XLocks::ttl("testa"));

//手动解锁
var_dump(\Lit\RedisExt\XLocks::unLock("testa"));

var_dump(\Lit\RedisExt\XLocks::ttl("testa"));