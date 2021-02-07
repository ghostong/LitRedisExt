<?php

namespace Lit\RedisExt;

/**
 * 循环计数器
 */
class LoopCounter extends RedisExt
{
    /**
     * 初始化 LoopCounter
     * @date 2021/2/4
     * @param mixed $redisHandler redis链接句柄
     * @return LoopCounter
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 分钟级循环计数器
     * @date 2021/2/4
     * @param string $key redisKey
     * @param int $minutes 循环周期, 分钟
     * @param bool $onTheMinute 是否完成的分钟 true 整点的, false 从当前开始
     * @return int 当前计数器数值
     * @throws \Exception
     * @author litong
     */
    public static function everyMinutes($key, $minutes, $onTheMinute = true) {
        $time = time();
        if ($onTheMinute) {
            $expire = (floor($time / ($minutes * 60)) + 1) * $minutes * 60;
        } else {
            $expire = $minutes * 60 + $time;
        }
        return self::loopCounter($key, $expire);
    }

    /**
     * 小时级循环计数器
     * @date 2021/2/4
     * @param string $key redisKey
     * @param int $hours 循环周期, 小时
     * @param bool $onTheHour 是否完成的小时 true 整点的, false 从当前开始
     * @return int 当前计数器数值
     * @throws \Exception
     * @author litong
     */
    public static function everyHours($key, $hours, $onTheHour = true) {
        $time = time();
        if ($onTheHour) {
            $expire = (floor($time / ($hours * 3600)) + 1) * $hours * 3600;
        } else {
            $expire = $hours * 3600 + $time;
        }
        return self::loopCounter($key, $expire);
    }

    /**
     * 天级循环计数器
     * @date 2021/2/4
     * @param string $key redisKey
     * @param int $days 循环周期, 天
     * @param bool $allDay 是否完整的天 true 完整的, false 从当前开始
     * @return int 当前计数器数值
     * @throws \Exception
     * @author litong
     */
    public static function everyDays($key, $days, $allDay = true) {
        $time = time();
        if ($allDay) {
            $expire = (floor($time / ($days * 86400)) + 1) * $days * 86400;
        } else {
            $expire = $days * 86400 + $time;
        }
        return self::loopCounter($key, $expire);
    }

    /**
     * 自主设置过期时间戳的循环计数器
     * @date 2021/2/4
     * @param string $key redisKey
     * @param int $expire 循环截止日期, 时间戳
     * @return int 当前计数器数值
     * @throws \Exception
     * @author litong
     */
    public static function nextRoundAt($key, $expire) {
        return self::loopCounter($key, $expire);
    }

    /**
     * 获取当前计数器数值
     * @date 2021/2/4
     * @param $key
     * @return int
     * @throws \Exception
     * @author litong
     */
    public static function get($key) {
        $count = self::redisHandler()->get($key);
        return $count ? intval($count) : 0;
    }

    /**
     * 销毁当前计数器
     * @date 2021/2/4
     * @param $key
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function destroy($key) {
        return self::redisHandler()->delete($key) > 0;
    }

    /**
     * 计数器主逻辑
     * @date 2021/2/4
     * @param $key
     * @param $expire
     * @return int
     * @throws \Exception
     * @author litong
     */
    private static function loopCounter($key, $expire) {
        $count = self::redisHandler()->incr($key);
        if ($count === 1 || $count === -1) {
            self::redisHandler()->expireAt($key, $expire);
        }
        return $count > 0 ? $count : 0;
    }
}