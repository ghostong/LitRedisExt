<?php


namespace Lit\RedisExt;

/**
 * 循环限流器
 */
class LoopThrottle extends RedisExt
{

    /**
     * 初始化 LoopThrottle
     * @date 2021/2/7
     * @param mixed $redisHandler redis链接句柄
     * @return LoopThrottle
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     *
     * @date 2021/2/7
     * @param string $key
     * @param int $limit
     * @param int $ttl
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function attempt($key, $limit, $ttl) {
        $lua = 'local key=KEYS[1] local limit=tonumber(ARGV[1]) local ttl=tonumber(ARGV[2]) ' .
            'local nt=redis.call(\'TIME\') local nts=nt[1]*1000000+nt[2] ' .
            'redis.call(\'ZREMRANGEBYSCORE\',key,0,nts-ttl*1000000) local card=redis.call(\'ZCARD\',key) ' .
            'if card>=limit then return 0 else return redis.call(\'ZADD\',key,nts,nts)>0 and redis.call(\'EXPIRE\',key,ttl)>0 end';
        return self::redisHandler()->eval($lua, [$key, $limit, $ttl], 1) > 0;
    }

    /**
     * 获取限流器当前访问量
     * @date 2021/2/6
     * @param string $key
     * @param $ttl
     * @return int
     * @throws \Exception
     * @author litong
     */
    public static function count($key, $ttl) {
        $lua = 'local key=KEYS[1] local ttl=tonumber(ARGV[1]) local nt=redis.call(\'TIME\') ' .
            'local nts=nt[1]*1000000+nt[2] return redis.call(\'ZCOUNT\',key,nts-ttl*1000000,nts)';
        return self::redisHandler()->eval($lua, [$key, $ttl], 1);
    }

    /**
     * 销毁当前限流器
     * @date 2021/2/7
     * @param string $key
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function destroy($key) {
        return self::redisHandler()->del($key) > 0;
    }

}