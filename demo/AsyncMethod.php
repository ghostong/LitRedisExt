<?php

namespace Demo;

include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 异步方法调用
 */

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");

//初始化链接
\Lit\RedisExt\AsyncMethod::init($redisHandler, 5);

//增加一个异步调用
$uniqId = \Lit\RedisExt\AsyncMethod::add("testKey", new \Demo\DemoClass(), 'staticClass', ["a" => 1, "b" => 3]);

//运行一个任务
\Lit\RedisExt\AsyncMethod::run("testKey");

//获取一个任务的运行状态
var_dump(\Lit\RedisExt\AsyncMethod::getStatus($uniqId));

//获取任务列表
var_dump(\Lit\RedisExt\AsyncMethod::getList("testKey"));


//执行异步调用
while (\Lit\RedisExt\AsyncMethod::run("testKey")) {

}

class DemoClass
{
    //方法被调用时,会自动在最后增加一个 $_uniqId 用于监控进程
    public static function staticClass($a, $b, $_uniqId) {
        var_dump(__CLASS__, __FUNCTION__, $a, $b, $_uniqId);
        self::staticClass2($a, $b, $_uniqId);
    }

    private static function staticClass2($a, $b, $_uniqId) {
        var_dump(__CLASS__, __FUNCTION__, $a, $b, $_uniqId);
    }

    public function className($a, $b, $_uniqId) {
        var_dump(__CLASS__, __FUNCTION__, $a, $b, $_uniqId);
        $this->className2($a, $b, $_uniqId);
    }

    private function className2($a, $b, $_uniqId) {
        var_dump(__CLASS__, __FUNCTION__, $a, $b, $_uniqId);
    }
}


