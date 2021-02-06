# RedisExt
通过redis实现一些常用功能


## 循环计数器
````php
//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");

//初始化计数器
Lit\RedisExt\RoundCounter::init($redisHandler);

//分钟计数器 完整的1分钟
var_dump(Lit\RedisExt\RoundCounter::everyMinutes("test1", 1, true));

//小时计数器 完整的2小时
var_dump(Lit\RedisExt\RoundCounter::everyHours("test2", 2, true));

//日期计数器 从当前开始3天
var_dump(Lit\RedisExt\RoundCounter::everyDays("test3", 3, false));

//指定时间计数器 指定某时间过期
var_dump(Lit\RedisExt\RoundCounter::nextRoundAt("test4", time() + 3600 * 7));

//销毁一个计数器
var_dump(Lit\RedisExt\RoundCounter::destroy("test3"));

//获取一个计数器数值
var_dump(Lit\RedisExt\RoundCounter::get("test4"));
````
