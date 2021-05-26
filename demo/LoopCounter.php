<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

/**
 * 循环计数器
 */

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");

//初始化计数器
Lit\RedisExt\LoopCounter::init($redisHandler);

//分钟计数器 完整的1分钟
var_dump(Lit\RedisExt\LoopCounter::everyMinutes("test1", 1, true));

//小时计数器 完整的2小时
var_dump(Lit\RedisExt\LoopCounter::everyHours("test2", 2, true));

//日期计数器 从当前开始3天
var_dump(Lit\RedisExt\LoopCounter::everyDays("test3", 3, false));

//指定某时间过期
var_dump(Lit\RedisExt\LoopCounter::nextRoundAt("test4", time() + 3600 * 7));

//销毁一个定时器
var_dump(Lit\RedisExt\LoopCounter::destroy("test3"));

//获取一个定时器数值
var_dump(Lit\RedisExt\LoopCounter::get("test4"));


