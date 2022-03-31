<?php

namespace Lit\RedisExt;

use Lit\RedisExt\Mapper\SingleMessageMapper;
use Lit\RedisExt\MessageStore\GroupMessage;
use Lit\RedisExt\MessageStore\Message;
use Lit\RedisExt\MessageStore\SendMessage;
use Lit\RedisExt\MessageStore\SingleMessage;

class MessageStore extends RedisExt
{
    /**
     * 初始化消息数据库
     * @date 2022/3/3
     * @param mixed $redisHandler redis链接句柄
     * @return MessageStore
     * @author litong
     */
    public static function init($redisHandler) {
        parent::init($redisHandler);
        return new static();
    }


    /**
     * 独立消息
     * @date 2022/3/29
     * @return SingleMessage
     * @throws \Exception
     * @author litong
     */
    public static function single() {
        return new SingleMessage(self::redisHandler());
    }

    /**
     * 分组消息
     * @date 2022/3/29
     * @return GroupMessage
     * @throws \Exception
     * @author litong
     */
    public static function group() {
        return new GroupMessage(self::redisHandler());
    }

    /**
     * 执行消费命令
     * @date 2022/3/30
     * @param callable $singleCallback
     * @param callable $groupCallback
     * @return void
     * @throws \Exception
     * @author litong
     */
    public static function run(callable $singleCallback, callable $groupCallback) {
        (new SendMessage(self::redisHandler()))->run($singleCallback, $groupCallback);
    }


}