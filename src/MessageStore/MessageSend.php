<?php

namespace Lit\RedisExt\MessageStore;

use Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper;

class MessageSend extends Message
{
    public function __construct($redisHandler) {
        parent::__construct($redisHandler);
    }

    public function run($singleCallback = null, $groupCallback = null) {
        $data = $this->popData();
        if (!empty($data[MessageSingleMapper::MESSAGE_TYPE])) {
            foreach ($data[MessageSingleMapper::MESSAGE_TYPE] as $value) {
                call_user_func($singleCallback, $value[0], $value[1]);
            }
        }
        if (!empty($data[MessageGroupMapper::MESSAGE_TYPE])) {
            foreach ($data[MessageGroupMapper::MESSAGE_TYPE] as $value) {
                call_user_func($groupCallback, $value[0], $value[1]);
            }
        }
    }

}