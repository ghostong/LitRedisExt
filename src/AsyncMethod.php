<?php


namespace Lit\RedisExt;

/**
 * 异步方法调用
 * @date 2021/5/25
 * @author litong
 */
class AsyncMethod extends RedisExt
{

    /**
     * 初始化异步回调
     * @date 2021/5/25
     * @param mixed $redisHandler redis链接句柄
     * @return AsyncMethod
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 增加一条异步回调
     * @date 2021/5/25
     * @param string $redisKey 指定一个RedisKey
     * @param string $namespace 要执行的对象的命名空间
     * @param string $class 要执行方法的类名
     * @param string $method 要执行的方法名称
     * @param array $param 调用的所有参数 (注意,此参数会在执行是增加一个 _uniqId 下标的唯一ID,供使用者对进程进行监控, 详见demo)
     * @return string
     * @throws \Exception
     * @author litong
     */
    public static function add($redisKey, $namespace, $class, $method, $param) {
        $param["_uniqId"] = uniqid("t-");
        $rPush = self::redisHandler()->lpush($redisKey, serialize(["namespace" => $namespace, "class" => $class, "method" => $method, "param" => $param]));
        return $rPush ? $param["_uniqId"] : "";
    }

    /**
     * 执行异步回调 每次执行一条
     * @date 2021/5/25
     * @param string $redisKey 指定一个RedisKey
     * @return mixed|false
     * @throws \Exception
     * @author litong
     */
    public static function run($redisKey) {
        $serialize = self::redisHandler()->rpop($redisKey);
        return self::call($serialize);
    }

    /**
     * 异步执行所有回调 每次执行队列中所有
     * @date 2021/5/26
     * @param string $redisKey 指定一个RedisKey
     * @return void
     * @throws \Exception
     * @author litong
     */
    public static function runAll($redisKey) {
        while ($serialize = self::redisHandler()->rpop($redisKey)) {
            self::call($serialize);
        }
    }

    /**
     * 阻塞模式监听, 执行所有回调
     * @date 2021/5/26
     * @param string $redisKey 指定一个RedisKey
     * @return void
     * @author litong
     */
    public static function runBlock($redisKey) {
        while (true) {
            $serialize = self::redisHandler()->brPop($redisKey, 10);
            self::call($serialize[1]);
        }
    }

    /**
     * 执行调用
     * @date 2021/5/26
     * @param $serialize
     * @return mixed|false
     * @author litong
     */
    private static function call($serialize) {
        if (empty($serialize)) {
            return false;
        }
        $data = unserialize($serialize);
        if (empty($data)) {
            return false;
        }
        return call_user_func_array([$data["namespace"] . $data["class"], $data["method"]], $data["param"]);
    }

}