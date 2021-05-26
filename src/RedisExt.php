<?php

namespace Lit\RedisExt;

class RedisExt
{

    private static $redisHandler = null;

    protected static function init($redisHandler) {
        self::$redisHandler = $redisHandler;
    }

    /**
     * @return \Redis
     * @throws \Exception
     */
    protected static function redisHandler() {
        if (self::$redisHandler !== null) {
            return self::$redisHandler;
        } else {
            throw new \Exception("请使用 init 初始化!!", 1);
        }
    }

}