<?php

namespace Lit\RedisExt;

/**
 * 带软过期刷新的缓存
 */
class RefreshCache extends RedisExt
{
    /**
     * @param mixed $redisHandler redis连接句柄
     * @return RefreshCache
     */
    public static function init($redisHandler)
    {
        parent::init($redisHandler);
        return new static();
    }

    protected static function load($cacheKey, $callback, $hardTtl)
    {
        try {
            $data = call_user_func($callback);
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            if ($json === false || !self::redisHandler()->set($cacheKey, $json, $hardTtl)) {
                return [false, null];
            }
            return [true, $data];
        } catch (\Exception $exception) {
            return [false, null];
        }
    }

    /**
     * 获取带软过期刷新的缓存数据
     * @param string $cacheKey 缓存key
     * @param callable $callback 数据查询回调
     * @param mixed $default 缓存未初始化时返回的默认值
     * @param int $ttl 数据允许的缓存时间（秒）
     * @return mixed
     * @throws \Exception
     */
    public static function get($cacheKey, $callback, $default = [], $ttl = 300)
    {
        $ttl = intval($ttl);
        $ttl = $ttl > 0 ? $ttl : 300;
        $redis = self::redisHandler();
        $hardTtl = $ttl * 10;

        $cached = $redis->get($cacheKey);
        if ($cached === false) {
            $defaultJson = json_encode($default, JSON_UNESCAPED_UNICODE);
            if ($defaultJson === false) {
                throw new \Exception('RefreshCache default data json encode failed');
            }
            if (!$redis->set($cacheKey, $defaultJson, ['nx', 'ex' => $hardTtl])) {
                return $default;
            }
            $result = self::load($cacheKey, $callback, $hardTtl);
            return $result[0] ? $result[1] : $default;
        }

        $cachedData = json_decode($cached, true);
        if ($redis->ttl($cacheKey) > $hardTtl - $ttl) {
            return $cachedData;
        }

        $lua = 'if redis.call("TTL",KEYS[1])<=tonumber(ARGV[1]) then ' .
            'return redis.call("EXPIRE",KEYS[1],ARGV[2]) else return 0 end';
        if (!$redis->eval($lua, [$cacheKey, $hardTtl - $ttl, $hardTtl], 1)) {
            return $cachedData;
        }
        $result = self::load($cacheKey, $callback, $hardTtl);
        return $result[0] ? $result[1] : $cachedData;

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
