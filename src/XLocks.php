<?php

namespace Lit\RedisExt;

/**
 * 独占锁
 */
class XLocks extends RedisExt
{
    protected static $lockTokens = [];

    /**
     * 初始化消息数据库
     * @date 2022/9/9
     * @param mixed $redisHandler redis链接句柄
     * @return XLocks
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 获取redis锁
     * @date 2022/9/9
     * @param string $key 锁key
     * @param int $ttl 锁生命周期
     * @param bool $autoUnlock 使用结束后, 是否自动解锁(忽略ttl)
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function lock($key, $ttl = 0, $autoUnlock = false) {
        $redis = self::redisHandler();
        $token = uniqid(getmypid() . '-', true) . '-' . mt_rand();
        if ($ttl > 0) {
            $locked = $redis->set($key, $token, ['nx', 'ex' => intval($ttl)]);
        } else {
            $locked = $redis->setNx($key, $token);
        }
        if (!$locked) {
            return false;
        }
        self::$lockTokens[$key] = $token;
        if ($autoUnlock) {
            register_shutdown_function(function ($redisHandler, $k, $lockToken) {
                self::releaseLock($redisHandler, $k, $lockToken);
                if (isset(self::$lockTokens[$k]) && self::$lockTokens[$k] === $lockToken) {
                    unset(self::$lockTokens[$k]);
                }
            }, $redis, $key, $token);
        }
        return true;
    }

    /**
     * 手动解锁
     * @date 2022/9/9
     * @param string $key 锁key
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function unLock($key) {
        if (!isset(self::$lockTokens[$key])) {
            return false;
        }
        $token = self::$lockTokens[$key];
        $result = self::releaseLock(self::redisHandler(), $key, $token);
        if (isset(self::$lockTokens[$key]) && self::$lockTokens[$key] === $token) {
            unset(self::$lockTokens[$key]);
        }
        return $result;
    }

    protected static function releaseLock($redis, $key, $token) {
        $lua = 'if redis.call("GET", KEYS[1]) == ARGV[1] then ' .
            'return redis.call("DEL", KEYS[1]) else return 0 end';
        return $redis->eval($lua, [$key, $token], 1) > 0;
    }

    /**
     * 获取锁剩余生存时间
     * @date 2022/9/9
     * @param string $key 锁key
     * @return int
     * @throws \Exception
     * @author litong
     */
    public static function ttl($key) {
        $ttl = self::redisHandler()->ttl($key);
        return $ttl > 0 ? intval($ttl) : 0;
    }

}
