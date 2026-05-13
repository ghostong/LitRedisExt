<?php


namespace Lit\RedisExt;

use Lit\RedisExt\Structs\SqlCacheReturn;

class SqlCache extends RedisExt
{
    protected static $lockKeyPrefix = 'sqlcache:lock:';

    public static function init($redisHandler)
    {
        parent::init($redisHandler);
        return new static();
    }

    protected static function loadData($cacheKey, $callback, $ttl)
    {
        $redis = self::redisHandler();
        $lockKey = self::$lockKeyPrefix . $cacheKey;
        XLocks::init($redis);
        if (XLocks::lock($lockKey, 10, true)) {
            if (!$redis->exists($cacheKey)) {
                $allData = call_user_func($callback);
                if (!is_array($allData)) {
                    $allData = [];
                }
                $redis->del($cacheKey);
                if (!empty($allData)) {
                    $pipe = $redis->pipeline();
                    foreach ($allData as $item) {
                        $pipe->sadd($cacheKey, json_encode($item, JSON_UNESCAPED_UNICODE));
                    }
                    $pipe->expire($cacheKey, $ttl);
                    $pipe->exec();
                }
                XLocks::unLock($lockKey);
                return $allData;
            }
        }
        return null;
    }

    /**
     * 顺序分页读取
     * @param string $cacheKey 缓存key
     * @param callable $callback 数据查询回调，返回完整数据数组
     * @param int $skip 跳过数量
     * @param int $limit 查询数量
     * @param int $ttl 缓存过期时间（秒）
     * @return SqlCacheReturn
     * @throws \Exception
     */
    public static function inOrder($cacheKey, $callback, $skip = 0, $limit = 10, $ttl = 3600)
    {
        $redis = self::redisHandler();
        $exists = $redis->exists($cacheKey);
        if (!$exists) {
            $allData = self::loadData($cacheKey, $callback, $ttl);
            if ($allData !== null) {
                $total = count($allData);
                $pageData = array_slice($allData, $skip, $limit);
                return new SqlCacheReturn($pageData, $total, $skip, $limit);
            }
        }
        $allMembers = $redis->smembers($cacheKey);
        $allData = array_map(function ($item) {
            return json_decode($item, true);
        }, $allMembers ?: []);

        $total = count($allData);
        $pageData = array_slice($allData, $skip, $limit);
        return new SqlCacheReturn($pageData, $total, $skip, $limit);
    }

    /**
     * 随机读取（数据保留在缓存中）
     * @param string $cacheKey 缓存key
     * @param callable $callback 数据查询回调
     * @param int $limit 查询数量
     * @param int $ttl 缓存过期时间（秒）
     * @return SqlCacheReturn
     * @throws \Exception
     */
    public static function inRandom($cacheKey, $callback, $limit = 10, $ttl = 3600)
    {
        $redis = self::redisHandler();
        $exists = $redis->exists($cacheKey);
        if (!$exists) {
            $allData = self::loadData($cacheKey, $callback, $ttl);
            if ($allData !== null) {
                $total = count($allData);
                if ($total > 0 && $limit > 0) {
                    shuffle($allData);
                    $pageData = array_slice($allData, 0, $limit);
                } else {
                    $pageData = [];
                }
                return new SqlCacheReturn($pageData, $total, 0, $limit);
            }
        }
        $total = $redis->scard($cacheKey);
        $data = $redis->srandmember($cacheKey, $limit);
        if ($data === false || $data === null) {
            $data = [];
        }
        $decodedData = array_map(function ($item) {
            return json_decode($item, true);
        }, $data);
        return new SqlCacheReturn($decodedData, $total, 0, $limit);
    }

    /**
     * 随机弹出（读取后从缓存中删除，缓存为空时重新加载）
     * @param string $cacheKey 缓存key
     * @param callable $callback 数据查询回调
     * @param int $limit 查询数量
     * @param int $ttl 缓存过期时间（秒）
     * @return SqlCacheReturn
     * @throws \Exception
     */
    public static function inRandomPopUp($cacheKey, $callback, $limit = 10, $ttl = 3600)
    {
        $redis = self::redisHandler();
        $exists = $redis->exists($cacheKey);
        $total = $redis->scard($cacheKey);
        if (!$exists || $total <= 0) {
            self::loadData($cacheKey, $callback, $ttl);
        }
        $data = $redis->spop($cacheKey, $limit);
        if ($data === null || $data === false) {
            $data = [];
        }
        $decodedData = array_map(function ($item) {
            return json_decode($item, true);
        }, $data);
        return new SqlCacheReturn($decodedData, $total, 0, $limit);
    }

    /**
     * 清除缓存
     * @param string $cacheKey 缓存key
     * @return bool
     * @throws \Exception
     */
    public static function clear($cacheKey)
    {
        return self::redisHandler()->del($cacheKey) > 0;
    }
}
