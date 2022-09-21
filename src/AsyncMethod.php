<?php


namespace Lit\RedisExt;

/**
 * 异步方法调用
 * @date 2021/5/25
 * @author litong
 */
class AsyncMethod extends RedisExt
{
    const STATUS_WAITING = 0; //等待中
    const STATUS_RUNNING = 1; //运行中
    const STATUS_SUCCESS = 2; //成功
    const STATUS_ERROR = -1;  //失败

    protected static $uniqRedisPrefix = "RedisExt:AsyncMethod:Status:";
    protected static $collectLen = 0;

    /**
     * 初始化异步方法
     * @date 2021/5/25
     * @param mixed $redisHandler redis链接句柄
     * @param int $collectionsLength 内置固定集合长度, 用于保存执行历史
     * @return AsyncMethod
     * @author litong
     */
    public static function init($redisHandler, $collectionsLength = 0) {
        parent::init($redisHandler);
        self::$collectLen = $collectionsLength;
        return new static();
    }

    /**
     * 增加一条异步方法调用
     * @date 2021/5/25
     * @param string $redisKey 指定一个RedisKey
     * @param string $object 要执行的对象的命名空间
     * @param string $method 要执行的方法名称
     * @param array $param 调用的所有参数 (注意,此参数会在执行是增加一个 _uniqId 下标的唯一ID,供使用者对进程进行监控, 详见demo)
     * @return string
     * @throws \Exception
     * @author litong
     */
    public static function add($redisKey, $object, $method, $param) {
        $param["_uniqId"] = uniqid("t-");
        $rPush = self::redisHandler()->lpush($redisKey, serialize(["object" => $object, "method" => $method, "param" => $param]));
        if ($rPush) {
            self::setStatus($param["_uniqId"], self::STATUS_WAITING);
            self::setList($redisKey, $param["_uniqId"]);
            return $param["_uniqId"];
        } else {
            return "";
        }
    }

    /**
     * 获取异步进程运行状态
     * @date 2022/9/21
     * @param string $uniqId 进程唯一ID
     * @return int|false
     * @throws \Exception
     * @author litong
     */
    public static function getStatus($uniqId) {
        $key = self::$uniqRedisPrefix . $uniqId;
        return self::redisHandler()->get($key);
    }

    /**
     * 设置异步进程运行状态
     * @date 2022/9/21
     * @param string $uniqId 进程唯一ID
     * @param int $status 进程状态
     * @return bool
     * @throws \Exception
     * @author litong
     */
    protected static function setStatus($uniqId, $status) {
        if (empty($uniqId)) {
            return false;
        }
        $key = self::$uniqRedisPrefix . $uniqId;
        return self::redisHandler()->set($key, $status, 3600 * 24 * 7);
    }

    /**
     * 获取异步进程列表
     * @date 2022/9/21
     * @param string $redisKey 指定一个RedisKey, 同 add 方法一致
     * @param int $index 列表分页 索引
     * @param int $limit 列表分页 显示条数
     * @return array
     * @throws \Exception
     * @author litong
     */
    public static function getList($redisKey, $index = 0, $limit = 10) {
        CappedCollections::init(self::redisHandler());
        return CappedCollections::get($redisKey . ":list", $index, $limit);
    }

    /**
     * 保存异步进程列表
     * @date 2022/9/21
     * @param $redisKey
     * @param $uniqId
     * @return bool
     * @throws \Exception
     * @author litong
     */
    protected static function setList($redisKey, $uniqId) {
        if (self::$collectLen < 1) {
            return false;
        }
        CappedCollections::init(self::redisHandler());
        return CappedCollections::set($redisKey . ":list", json_encode(['uniqId' => $uniqId, 'timestamp' => time()]), self::$collectLen) > 0;
    }

    /**
     * 执行异步方法调用 每次执行一条
     * @date 2021/5/25
     * @param string $redisKey 指定一个RedisKey 同 add
     * @return array
     * @throws \Exception
     * @author litong
     */
    public static function run($redisKey) {
        $serialize = self::redisHandler()->rpop($redisKey);
        if (empty($serialize)) {
            return [];
        }
        $data = unserialize($serialize);
        if (empty($data)) {
            return [];
        }
        $uniqId = $data["param"]["_uniqId"];
        try {
            self::setStatus($uniqId, self::STATUS_RUNNING);
            $return = @call_user_func_array([$data["object"], $data["method"]], $data["param"]);
            self::setStatus($uniqId, self::STATUS_SUCCESS);
            return ["return" => $return, "callable" => $data];
        } catch (\Exception $exception) {
            self::setStatus($uniqId, self::STATUS_ERROR);
            throw $exception;
        }
    }


}