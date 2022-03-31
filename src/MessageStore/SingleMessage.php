<?php

namespace Lit\RedisExt\MessageStore;


use Lit\RedisExt\Mapper\SingleMessageMapper;

class SingleMessage extends Message
{
    const MESSAGE_TYPE = "s";
    private $message = null;
    private $sender = null;

    public function __construct($redisHandler) {
        parent::__construct($redisHandler);
    }

    public function setMessage(SingleMessageMapper $message) {
        $this->message = $this->messageFormat($message, self::MESSAGE_TYPE);
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