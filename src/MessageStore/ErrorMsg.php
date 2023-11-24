<?php

namespace Lit\RedisExt\MessageStore;

class ErrorMsg
{
    protected static $errorCode = 0;
    protected static $errorMessage = "";


    /**
     * 错误消息
     * @date 2022/3/31
     * @param $errorCode
     * @param $errorMessage
     * @return void
     * @author litong
     */
    protected static function setError($errorCode, $errorMessage) {
        self::$errorCode = $errorCode;
        self::$errorMessage = $errorMessage;
    }

    /**
     * 获取错误代码
     * @date 2022/3/31
     * @return int
     * @author litong
     */
    public static function getErrorCode() {
        return self::$errorCode;
    }

    /**
     *  获取错误消息
     * @date 2022/3/31
     * @return string
     * @author litong
     */
    public static function getErrorMessage() {
        return self::$errorMessage;
    }

}