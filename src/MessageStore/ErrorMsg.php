<?php

namespace Lit\RedisExt\MessageStore;

class ErrorMsg
{
    protected $errorCode = 0;
    protected $errorMessage = "";


    /**
     * 错误消息
     * @date 2022/3/31
     * @param $errorCode
     * @param $errorMessage
     * @return void
     * @author litong
     */
    protected function setError($errorCode, $errorMessage) {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * 获取错误代码
     * @date 2022/3/31
     * @return int
     * @author litong
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     *  获取错误消息
     * @date 2022/3/31
     * @return string
     * @author litong
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

}