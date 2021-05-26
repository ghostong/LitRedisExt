<?php

namespace abc;

include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 异步方法调用
 */

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");

//初始化链接
\Lit\RedisExt\AsyncMethod::init($redisHandler);

//增加一个异步调用
\Lit\RedisExt\AsyncMethod::add("testKey", "\abc\\", "ABC", 'bbc', ["a" => 1, "b" => 3]);

//执行一条异步调用
\Lit\RedisExt\AsyncMethod::run("testKey");

//执行所有异步调用
\Lit\RedisExt\AsyncMethod::runAll("testKey");

//阻塞执行所有异步调用
\Lit\RedisExt\AsyncMethod::runBlock("testKey");


class ABC
{
    //方法被调用时,会自动在最后增加一个 $_uniqId 用于监控进程
    function bbc($a, $b, $_uniqId) {
        var_dump($a, $b, $_uniqId);
    }
}
