<?php

namespace Lit\RedisExt;

class VersionString extends RedisExt
{

    /**
     * 初始化redis数据库
     * @date 2023/5/6
     * @param mixed $redisHandler redis链接句柄
     * @return VersionString
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    public static function get($key, $version = null) {

    }

    public static function mGet($keys, $version = null) {

    }

    public static function set($key, $value, $version = null) {

    }

    public static function mSet($keys, $values, $version = null) {

    }

    public static function getOrSet($key, $callable, $version = null) {
        $data = self::redisHandler()->get($key);
        if (false === $data) {
            $data = call_user_func($callable, $key);
//            var_dump($data);
        }
        return $data;
    }

    public static function mGetOrSet($keys, $callable, $version = null) {

    }


}