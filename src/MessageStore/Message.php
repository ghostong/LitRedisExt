<?php

namespace Lit\RedisExt\MessageStore;


use Lit\RedisExt\Mapper\GroupMessageMapper;
use Lit\RedisExt\Mapper\MessageMapper;
use Lit\RedisExt\Mapper\SenderMapper;
use Lit\RedisExt\Mapper\SingleMessageMapper;


class Message
{
    /** @var \Redis $redisHandler */
    protected $redisHandler = null;

    protected $errorCode = 0;
    protected $errorMessage = "";

    public $messageListKey = "message:store:zset:message:list";
    public $groupDataKey = "message:store:hset:group:data";
    public $singleDataKey = "message:store:hset:single:data";
    public $duplicateKey = "message:store:string:duplicate";

    public function __construct($redisHandler) {
        $this->redisHandler = $redisHandler;
    }

    protected function groupDataKey($topic) {
        return $this->groupDataKey . ":" . $topic;
    }

    protected function getMessageId(MessageMapper $message) {
        return $message->messageType . "_" . $message->topic . "_" . $message->uniqId;
    }

    protected function getMessagePointer(MessageMapper $message) {
        if ($message->messageType === SingleMessage::MESSAGE_TYPE) {
            return $this->getMessageId($message);
        } else {
            return $message->messageType . "_" . $message->topic;
        }
    }

    protected function messageIdDecode($messageId) {
        $return = [];
        list($return["type"], $return["topic"], $return["uniqId"]) = explode("_", $messageId);
        return $return;
    }

    protected function pointerDecode($pointer) {
        $return = [];
        list($return["type"], $return["topic"]) = explode("_", $pointer);
        return $return;
    }

    protected function pointerToType($pointer) {
        $pointerData = $this->pointerDecode($pointer);
        if (in_array($pointerData["type"], [SingleMessage::MESSAGE_TYPE, GroupMessage::MESSAGE_TYPE])) {
            return $pointerData["type"];
        } else {
            return SingleMessage::MESSAGE_TYPE;
        }
    }

    protected function messageToList(MessageMapper $message) {
        $this->redisHandler->zAdd($this->messageListKey, strtotime($message->sendTime), $this->getMessagePointer($message));
        return true;
    }

    protected function messageFormat(MessageMapper $message, $messageType) {
        $message->uniqId = is_null($message->uniqId) ? uniqid() : $message->uniqId;
        $message->topic = is_null($message->topic) ? "public" : $message->topic;
        $message->duplicateSecond = (is_numeric($message->duplicateSecond) && $message->duplicateSecond > 0) ? intval($message->duplicateSecond) : null;
        $message->messageType = $messageType;
        return $message;
    }

    protected function senderFormat(SenderMapper $sender) {
        return $sender;
    }

    protected function saveMessageBody(MessageMapper $message) {
        if ($message->messageType === SingleMessage::MESSAGE_TYPE) {
            $key = $this->singleDataKey;
        } else {
            $key = $this->groupDataKey($message->topic);
        }
        if ($this->redisHandler->hSet($key, $this->getMessageId($message), $message->body) !== false) {
            return true;
        } else {
            $this->setError(10101, __CLASS__ . " error");
            return false;
        }
    }

    protected function popData() {
        $time = time();
        $data = $this->redisHandler->zRangeByScore($this->messageListKey, 0, $time, ['withscores' => TRUE]);
        $this->redisHandler->zRemRangeByScore($this->messageListKey, 0, $time);
        $return = [
            SingleMessage::MESSAGE_TYPE => [],
            GroupMessage::MESSAGE_TYPE => []
        ];
        foreach ($data as $pointer => $timestamp) {
            $type = $this->pointerToType($pointer);
            if ($type === SingleMessage::MESSAGE_TYPE) {
                $return[SingleMessage::MESSAGE_TYPE][] = $this->popSingleData($pointer, $timestamp);
            } else {
                $return[GroupMessage::MESSAGE_TYPE][] = $this->popGroupData($pointer, $timestamp);
            }
        }
        return $return;
    }

    protected function popGroupData($pointer, $timestamp) {
        $info = $this->pointerDecode($pointer);
        $bodies = $this->redisHandler->hGetAll($this->groupDataKey($info['topic']));
        $this->redisHandler->del($this->groupDataKey($info['topic']));
        $return = [];
        foreach ($bodies as $messageId => $body) {
            $messageInfo = $this->messageIdDecode($messageId);
            $groupMapper = new GroupMessageMapper();
            $groupMapper->body = $body;
            $groupMapper->topic = $info["topic"];
            $groupMapper->uniqId = $messageInfo["uniqId"];
            $groupMapper->sendTime = date("Y-m-d H:i:s", $timestamp);
            $return[] = $groupMapper;
        }
        return $return;
    }

    protected function popSingleData($messageId, $timestamp) {
        $info = $this->messageIdDecode($messageId);
        $body = $this->redisHandler->hGet($this->singleDataKey, $messageId);
        $this->redisHandler->hDel($this->singleDataKey, $messageId);
        $singleMapper = new SingleMessageMapper();
        $singleMapper->body = $body;
        $singleMapper->topic = $info["topic"];
        $singleMapper->uniqId = $info["uniqId"];
        $singleMapper->sendTime = date("Y-m-d H:i:s", $timestamp);
        return $singleMapper;
    }

    protected function duplicateChecked(MessageMapper $message) {
        if (is_null($message->duplicateSecond)) {
            return true;
        } else {
            $messageId = $this->getMessageId($message);
            $key = $this->duplicateKey . ":" . $messageId;
            if ($this->redisHandler->setNx($key, 1)) {
                $this->redisHandler->expire($key, $message->duplicateSecond);
                return true;
            } else {
                $this->setError(10102, __CLASS__ . " error ");
                return false;
            }
        }
    }

    protected function setError($errorCode, $errorMessage) {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }
}