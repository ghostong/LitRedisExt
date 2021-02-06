<?php


namespace Lit\RedisExt;

/**
 * 固定集合
 */
class CappedCollections extends RedisExt
{
    /**
     * 初始化 CappedCollections
     * @date 2021/2/6
     * @param mixed $redisHandler redis链接句柄
     * @return CappedCollections
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 固定集合写入数据
     * @date 2021/2/6
     * @param string $key
     * @param string $value
     * @param int $size
     * @return int
     * @throws \Exception
     * @author litong
     */
    public static function set($key, $value, $size) {
        $lua = 'local key=KEYS[1] local value=ARGV[1] local size=tonumber(ARGV[2]) local v=redis.call(\'RPUSH\',key,value) ' .
            'if v>size then redis.call(\'LTRIM\',key,v-size,-1) return size else return v end';
        return self::redisHandler()->eval($lua, [$key, $value, $size], 1);
    }

    /**
     * 获取固定集合中的数据
     * @date 2021/2/6
     * @param string $key
     * @param int $index 大于等于0
     * @param int $limit 大于0
     * @return mixed
     * @throws \Exception
     * @author litong
     */
    public static function get($key, $index, $limit) {
        $range = self::redisHandler()->lrange($key, $index, $index + $limit - 1);
        $nextIndex = (self::size($key) == $index + count($range) || count($range) == 0) ? 0 : $index + count($range);
        return [
            "data" => $range,
            "nextIndex" => $nextIndex
        ];
    }

    /**
     * 获取固定集合中的数据量
     * @date 2021/2/6
     * @param string $key
     * @return int
     * @throws \Exception
     * @author litong
     */
    public static function size($key) {
        return self::redisHandler()->llen($key);
    }

    /**
     * 销毁固定集合
     * @date 2021/2/6
     * @param $key
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function destroy($key) {
        return self::redisHandler()->delete($key) > 0;
    }
}
