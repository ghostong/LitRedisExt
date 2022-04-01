<?php

namespace Lit\RedisExt\MessageStore;

use Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderMapper;

class MessageGroup extends Message
{

    public function __construct($redisHandler) {
        parent::__construct($redisHandler);
    }

    /**
     * 增加Message参数
     * @date 2022/3/31
     * @param MessageGroupMapper $message
     * @return Message
     * @author litong
     */
    public function setMessage(MessageGroupMapper $message) {
        $this->message = $this->messageFormat($message);
        return $this;
    }

    public function send() {
        if (!$this->duplicateChecked($this->message)) {
            return false;
        }
        if ($this->messageToList($this->message) && $this->saveBody($this->message, $this->sender)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 消息体保存至存储
     * @date 2022/3/31
     * @param MessageGroupMapper $message
     * @param SenderMapper|null $sender
     * @return bool
     * @author litong
     */
    protected function saveBody(MessageGroupMapper $message, SenderMapper $sender = null) {
        $key = $this->groupDataKey($message->topic);
        return $this->saveMessageBody($key, $message, $sender);
    }

}