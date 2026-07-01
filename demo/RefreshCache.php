<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");

//初始化带软过期刷新的缓存
\Lit\RedisExt\RefreshCache::init($redisHandler);

$getDataCallback = function () {
    return [
        ['id' => 1, 'name' => '张三'],
        ['id' => 2, 'name' => '李四'],
    ];
};

\Lit\RedisExt\RefreshCache::clear("refresh:users");

//首个请求写入默认值并查询数据库；并发请求未取得刷新权时立即返回默认值
$result1 = \Lit\RedisExt\RefreshCache::get("refresh:users", $getDataCallback, [], 300);
print_r($result1);

//5分钟内直接返回缓存；Redis中的实际过期时间为50分钟
$result2 = \Lit\RedisExt\RefreshCache::get("refresh:users", $getDataCallback, [], 300);
print_r($result2);
