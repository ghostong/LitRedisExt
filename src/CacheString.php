<?php

namespace Lit\RedisExt;

class CacheString extends RedisExt
{
    protected static $verField = "version";
    protected static $dataField = "date";

    /**
     * 初始化redis数据库
     * @date 2023/5/6
     * @param mixed $redisHandler redis链接句柄
     * @return CacheString
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 获取最新版的 cache 数据
     * @date 2023/5/7
     * @param $key
     * @param string $version
     * @return mixed|string|null
     * @throws \Exception
     * @author litong
     */
    public static function get($key, $version = "1.0.0") {
        $data = self::redisHandler()->get($key);
        if (false !== $data) {
            $data = json_decode($data, true);
            if (isset($data[self::$verField]) && $data[self::$verField] === $version) {
                return $data[self::$dataField];
            }
        }
        return null;
    }

    public static function mGet($keys, $version = "1.0.0") {

    }

    /**
     * 写入一个最新版的 cache 数据
     * @date 2023/5/7
     * @param string $key redis key
     * @param string|array $value 要存储的值
     * @param string $version 数据版本
     * @param int $timeout 数据生命期
     * @return mixed|string|null
     * @throws \Exception
     * @author litong
     */
    public static function set($key, $value, $version = "1.0.0", $timeout = 0) {
        $data = [self::$verField => $version, self::$dataField => $value];
        return self::redisHandler()->set($key, json_encode($data, JSON_UNESCAPED_UNICODE), $timeout);
    }

    public static function mSet($keys, $values, $version = null) {

    }

    /**
     * 获取或初始化一个缓存数据
     * @date 2023/5/7
     * @param string $key redis key
     * @param callable $callable 数据为空时, 初始化数据的方法
     * @param array $params callable 的参数
     * @param string $version 数据版本
     * @param int $timeout 数据生命期
     * @return false|mixed|string|null
     * @throws \Exception
     * @author litong
     */
    public static function getOrSet($key, $callable, $params, $version = "1.0.0", $timeout = 0) {
        $data = self::get($key, $version);
        if (is_null($data)) {
            $data = call_user_func_array($callable, $params);
            self::set($key, $data, $version, $timeout);

        }
        return $data;
    }

    public static function mGetOrSet($keys, $callable, $version = "1.0.0") {

    }


}