<?php

namespace Lit\RedisExt;


use Lit\RedisExt\MessageStore\MessageGroup;
use Lit\RedisExt\MessageStore\Mapper\MessageMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderMapper;

use Lit\RedisExt\MessageStore\Sender;
use Lit\RedisExt\MessageStore\MessageSend;
use Lit\RedisExt\MessageStore\MessageSingle;

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
     * @return MessageSingle
     * @throws \Exception
     * @author litong
     */
    public static function single() {
        return new MessageSingle(self::redisHandler());
    }

    /**
     * 分组消息
     * @date 2022/3/29
     * @return MessageGroup
     * @throws \Exception
     * @author litong
     */
    public static function group() {
        return new MessageGroup(self::redisHandler());
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
        (new MessageSend(self::redisHandler()))->run($singleCallback, $groupCallback);
    }

    /**
     * 自动执行
     * @date 2022/4/1
     * @throws \Exception
     * @author litong
     */
    public static function autoRun() {
        (new MessageSend(self::redisHandler()))->run(
            function (MessageMapper $message, SenderMapper $sender = null) {
                Sender::SendSingle($message, $sender);
            },
            function (array $message, array $sender = null) {
                Sender::DingSendGroup($message, $sender);
            }
        );
    }

}