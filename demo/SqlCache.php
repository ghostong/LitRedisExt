<?php

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");

//初始化 SQL Cache
\Lit\RedisExt\SqlCache::init($redisHandler);

//生成测试数据的回调函数
$getDataCallback = function () {
    return [
        ['id' => 1, 'name' => '张三', 'age' => 20],
        ['id' => 2, 'name' => '李四', 'age' => 25],
        ['id' => 3, 'name' => '王五', 'age' => 30],
        ['id' => 4, 'name' => '赵六', 'age' => 35],
        ['id' => 5, 'name' => '钱七', 'age' => 40],
        ['id' => 6, 'name' => '孙八', 'age' => 45],
        ['id' => 7, 'name' => '周九', 'age' => 50],
        ['id' => 8, 'name' => '吴十', 'age' => 55],
    ];
};


//1. inOrder - 顺序分页读取

//清除之前的缓存
\Lit\RedisExt\SqlCache::clear("sql:users:inorder");

//首次查询, 触发回调 (skip=0, limit=3)";
$result1 = \Lit\RedisExt\SqlCache::inOrder("sql:users:inorder", $getDataCallback, 0, 3, 3600);
print_r($result1->getData());
echo "Total: " . $result1->getTotal() . "\n\n";

//第二次查询, 走缓存 (skip=3, limit=3):\n"
$result2 = \Lit\RedisExt\SqlCache::inOrder("sql:users:inorder", $getDataCallback, 3, 3, 3600);
print_r($result2->getData());
echo "Total: " . $result2->getTotal() . "\n\n";


//2. inRandom - 随机读取（数据保留）

//清除之前的缓存
\Lit\RedisExt\SqlCache::clear("sql:users:inrandom");

//首次, 触发回调, 随机查询 (limit=3)
$result3 = \Lit\RedisExt\SqlCache::inRandom("sql:users:inrandom", $getDataCallback, 3, 3600);
print_r($result3->getData());
echo "Total: " . $result3->getTotal() . "\n\n";

//再次, 走缓存, 随机查询 (limit=3)
$result4 = \Lit\RedisExt\SqlCache::inRandom("sql:users:inrandom", $getDataCallback, 3, 3600);
print_r($result4->getData());
echo "Total: " . $result4->getTotal() . "\n\n";


//3. inRandomPopUp - 随机弹出（数据删除）

//清除之前的缓存
\Lit\RedisExt\SqlCache::clear("sql:users:pop");

//首次查询, 缓存, 弹出 (limit=3)
$result5 = \Lit\RedisExt\SqlCache::inRandomPopUp("sql:users:pop", $getDataCallback, 3, 3600);
print_r($result5->getData());
echo "Total before pop: " . $result5->getTotal() . "\n\n";

//第二次, 缓存, 弹出 (limit=3)
$result6 = \Lit\RedisExt\SqlCache::inRandomPopUp("sql:users:pop", $getDataCallback, 3, 3600);
echo "第二次弹出 (limit=3):\n";
print_r($result6->getData());
echo "Total before pop: " . $result6->getTotal() . "\n\n";

//第三次, 缓存, 弹出 (limit=3)
$result7 = \Lit\RedisExt\SqlCache::inRandomPopUp("sql:users:pop", $getDataCallback, 3, 3600);
print_r($result7->getData());
echo "Total before pop: " . $result7->getTotal() . "\n\n";

//第四次, 弹出,缓存为空, 重新加载 (limit=3)
$result8 = \Lit\RedisExt\SqlCache::inRandomPopUp("sql:users:pop", $getDataCallback, 3, 3600);
print_r($result8->getData());
echo "Total before pop: " . $result8->getTotal() . "\n\n";
