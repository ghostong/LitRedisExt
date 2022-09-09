<?php

namespace Lit\RedisExt;

/**
 * 独占锁
 */
class XLocks extends RedisExt
{
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
        if (self::redisHandler()->setNx($key, 1)) {
            if ($ttl > 0) {
                self::redisHandler()->expire($key, $ttl);
            }
            if ($autoUnlock) {
                register_shutdown_function(function ($k) {
                    self::redisHandler()->del($k);
                }, $key);
            }
            return true;
        } else {
            return false;
        }
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
        return self::redisHandler()->del($key) > 0;
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