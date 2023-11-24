<?php

namespace Lit\RedisExt\MessageStore;

use Lit\RedisExt\MessageStore\Mapper\MessageMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderMapper;

class Sender extends ErrorMsg
{

    /**
     *
     * @date 2022/4/1
     * @param MessageMapper $message
     * @param SenderMapper|null $sender
     * @return void
     * @author litong
     */
    public static function SendSingle(MessageMapper $message, SenderMapper $sender = null) {
        $sendType = constant(get_class($sender) . "::SENDER_TYPE");
        switch ($sendType) {
            // case email
            case SenderDingMapper::SENDER_TYPE: //DingDing
            default:
                return self::DingSingleSend($message, $sender);
        }
    }

    public static function DingSendGroup(array $message, $sender = null) {
        if (empty($sender)) {
            self::setError(10201, "sender 为必填参数");
            return false;
        }
        return call_user_func(
            ["Lit\RedisExt\MessageStore\Sender\DingGroup", constant(get_class($sender[count($sender) - 1]) . "::MSG_TYPE")],
            $message, $sender
        );
    }

    protected static function DingSingleSend(MessageMapper $message, SenderMapper $sender = null) {
        if (empty($sender)) {
            self::setError(10202, "sender 为必填参数");
            return false;
        }
        return call_user_func(
            ["Lit\RedisExt\MessageStore\Sender\DingSingle", constant(get_class($sender) . "::MSG_TYPE")],
            $message, $sender
        );
    }

}