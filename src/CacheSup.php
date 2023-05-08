<?php

namespace Lit\RedisExt;

use Lit\RedisExt\Structs\CacheSupGetKey;

class CacheSup extends RedisExt
{
    protected static $verField = "version";
    protected static $dataField = "date";

    /**
     * 初始化redis数据库
     * @date 2023/5/6
     * @param mixed $redisHandler redis链接句柄
     * @return CacheSup
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }

    /**
     * 获取缓存数据
     * @date 2023/5/7
     * @param string $key key
     * @param string $version 1.0.0
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

    /**
     * 批量获取缓存数据
     * @date 2023/5/8
     * @param array $keys [key1, key2 ...]
     * @param string $version 1.0.0
     * @return array
     * @throws \Exception
     * @author litong
     */
    public static function mGet($keys, $version = "1.0.0") {
        $data = self::redisHandler()->mget($keys);
        return array_combine($keys, array_map(function ($value) use ($version) {
            $deCode = json_decode($value, true);
            if (isset($deCode[self::$verField]) && $deCode[self::$verField] === $version) {
                return $deCode[self::$dataField];
            } else {
                return null;
            }
        }, $data));
    }

    /**
     * 写入缓存数据
     * @date 2023/5/7
     * @param string $key key1
     * @param string|array $value 要存储的值
     * @param string $version 数据版本
     * @param int $timeout 数据生命期
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function set($key, $value, $version = "1.0.0", $timeout = 0) {
        $data = [self::$verField => $version, self::$dataField => $value];
        return self::redisHandler()->set($key, json_encode($data, JSON_UNESCAPED_UNICODE), $timeout);
    }

    /**
     * 批量写入缓存数据
     * @date 2023/5/8
     * @param array $data 要存储的数据 [key1=>value1, key2=>value2 ...]
     * @param string $version
     * @param int $timeout
     * @return bool
     * @throws \Exception
     * @author litong
     */
    public static function mSet($data, $version = "1.0.0", $timeout = 0) {
        $data = array_map(function ($value) use ($version) {
            return json_encode([self::$verField => $version, self::$dataField => $value], JSON_UNESCAPED_UNICODE);
        }, $data);
        if ($timeout > 0) {
            $pipe = self::redisHandler()->pipeline();
            /**
             * @var \Redis $pipe redis
             */
            $pipe = $pipe->mset($data);
            foreach ($data as $key => $tmp) {
                $pipe = $pipe->expire($key, $timeout);
            }
            $exec = $pipe->exec();
            return count($exec) === array_sum($exec);
        } else {
            return self::redisHandler()->mset($data);
        }
    }

    /**
     * 获取或初始化一个缓存数据
     * @date 2023/5/7
     * @param CacheSupGetKey $keyObject RedisKey对象
     * @param callable $callable 数据为空时, 初始化数据的方法
     * @param string $version 数据版本
     * @param int $timeout 数据生命期
     * @return false|mixed|string|null
     * @throws \Exception
     * @author litong
     */
    public static function getOrSet($keyObject, $callable, $version = "1.0.0", $timeout = 0) {
        $data = self::get($keyObject->getKey(), $version);
        if (is_null($data)) {
            $data = call_user_func_array($callable, $keyObject->getParams());
            self::set($keyObject->getKey(), $data, $version, $timeout);
        }
        return $data;
    }

    /**
     * 批量获取或初始化缓存数据
     * @date 2023/5/8
     * @param CacheSupGetKey[] $keyObjects RedisKey对象
     * @param $callable
     * @param string $version
     * @param int $timeout
     * @return array
     * @author litong
     */
    public static function mGetOrSet($keyObjects, $callable, $version = "1.0.0", $timeout = 0) {
        $keyInfos = [];
        foreach ($keyObjects as $keyObject) {
            $keyInfos[$keyObject->getKey()] = $keyObject;
        }
        $data = self::mGet(array_keys($keyInfos), $version);
        $toCathe = [];
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $data[$key] = $toCathe[$key] = call_user_func_array($callable, $keyInfos[$key]->getParams());
            }
        }
        if (!empty($toCathe)) {
            self::mSet($toCathe, $version, $timeout);
        }
        return $data;
    }


}