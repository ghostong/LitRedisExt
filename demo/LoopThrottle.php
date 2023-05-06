<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 循环限流器
 */

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("utils-redis");

//初始化限流器
\Lit\RedisExt\LoopThrottle::init($redisHandler);

//尝试进行限流访问
var_dump(\Lit\RedisExt\LoopThrottle::attempt("tKey1", 2, 10));

//查询限流
var_dump(\Lit\RedisExt\LoopThrottle::count("tKey1", 300));

//销毁限流器
var_dump(\Lit\RedisExt\LoopThrottle::destroy("tKey1"));



