<?php

namespace Lit\RedisExt\MessageStore;

use Lit\RedisExt\Mapper\GroupMessageMapper;

class GroupMessage extends Message
{
    const MESSAGE_TYPE = "g";
    private $message = null;

    public function __construct($redisHandler) {
        parent::__construct($redisHandler);
    }

    public function setMessage(GroupMessageMapper $message) {
        $this->message = $this->messageFormat($message, self::MESSAGE_TYPE);
        return $this;
    }

    public function setSendOpt() {
        return $this;
    }

    public function send() {
        if (!$this->duplicateChecked($this->message)) {
            return false;
        }
        if ($this->messageToList($this->message) && $this->saveMessageBody($this->message)) {
            return true;
        } else {
            return false;
        }
    }

}