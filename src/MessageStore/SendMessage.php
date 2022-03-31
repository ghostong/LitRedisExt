<?php

namespace Lit\RedisExt\MessageStore;

class SendMessage extends Message
{
    public function __construct($redisHandler) {
        parent::__construct($redisHandler);
    }

    public function run($singleCallback = null, $groupCallback = null) {
        $data = $this->popData();
        if (!empty($data[SingleMessage::MESSAGE_TYPE])) {
            call_user_func($singleCallback, $data[SingleMessage::MESSAGE_TYPE]);
        }
        if (!empty($data[GroupMessage::MESSAGE_TYPE])) {
            call_user_func($groupCallback, $data[GroupMessage::MESSAGE_TYPE]);
        }
    }

}