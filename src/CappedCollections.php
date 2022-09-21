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
        $num = self::redisHandler()->rpush($key, $value);
        if ($num > $size) {
            self::redisHandler()->lTrim($key, $num - $size, -1);
            return $size;
        } else {
            return $num;
        }
    }

    /**
     * 获取固定集合中的数据
     * @date 2021/2/6
     * @param string $key
     * @param int $index 大于等于0
     * @param int $limit 大于0
     * @return array
     * @throws \Exception
     * @author litong
     */
    public static function get($key, $index, $limit) {
        $range = self::redisHandler()->lrange($key, $index, $index + $limit - 1);
        $count = self::size($key);
        $nextIndex = ($count == $index + count($range) || count($range) == 0) ? 0 : $index + count($range);
        return [
            "data" => $range,
            "nextIndex" => $nextIndex,
            "count" => $count
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
        return self::redisHandler()->del($key) > 0;
    }
}
