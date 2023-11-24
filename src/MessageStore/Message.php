<?php

namespace Lit\RedisExt\MessageStore;


use Lit\RedisExt\MessageStore\Mapper\SenderDingMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper;
use Lit\RedisExt\MessageStore\Sender\Ding;


class Message extends ErrorMsg
{
    /** @var \Redis $redisHandler */
    protected $redisHandler = null;
    protected $message = null;
    protected $sender = null;

    /**
     * message队列redisKey
     */
    public $messageListKey = "message:store:zset:message:list";

    /**
     * 分组消息数据存储key前缀
     */
    public $groupDataKey = "message:store:hset:group:data";

    /**
     * 独立消息存储key
     */
    public $singleDataKey = "message:store:hset:single:data";

    /**
     * 消息排重redis key 前缀
     */
    public $duplicateKey = "message:store:string:duplicate";


    public function __construct($redisHandler) {
        $this->redisHandler = $redisHandler;
    }

    /**
     * 增加发送参数
     * @date 2022/3/31
     * @param SenderMapper $sender
     * @return Message
     * @author litong
     */
    public function setSender(SenderMapper $sender) {
        $this->sender = $this->senderFormat($sender);
        return $this;
    }

    /**
     * 分组消息数据存储redis key
     * @date 2022/3/31
     * @param $topic
     * @return string
     * @author litong
     */
    protected function groupDataKey($topic) {
        return $this->groupDataKey . ":" . $topic;
    }

    /**
     * 通过message对象获取message 唯一消息ID
     * @date 2022/3/31
     * @param MessageMapper $message
     * @return string
     * @author litong
     */
    protected function getMessageId(MessageMapper $message) {
        $messageType = constant(get_class($message) . "::MESSAGE_TYPE");
        return $messageType . "_" . $message->topic . "_" . $message->uniqId;
    }

    /**
     * 通过message对象获取队列唯一指针值
     * @date 2022/3/31
     * @param MessageMapper $message
     * @return string
     * @author litong
     */
    protected function getMessagePointer(MessageMapper $message) {
        $messageType = constant(get_class($message) . "::MESSAGE_TYPE");
        if ($messageType === MessageSingleMapper::MESSAGE_TYPE) {
            return $this->getMessageId($message);
        } else {
            return $messageType . "_" . $message->topic;
        }
    }

    /**
     * 消息指针值 转换 消息类型, 消息topic
     * @date 2022/3/31
     * @param $pointer
     * @return array
     * @author litong
     */
    protected function pointerDecode($pointer) {
        list($return["type"], $return["topic"]) = explode("_", $pointer);
        return $return;
    }

    /**
     * 消息指针值 转 消息类型
     * @date 2022/3/31
     * @param $pointer
     * @return string
     * @author litong
     */
    protected function pointerToType($pointer) {
        $pointerData = $this->pointerDecode($pointer);
        if (in_array($pointerData["type"], [MessageSingleMapper::MESSAGE_TYPE, MessageGroupMapper::MESSAGE_TYPE])) {
            return $pointerData["type"];
        } else {
            return MessageSingleMapper::MESSAGE_TYPE;
        }
    }

    /**
     * 格式化 消息体
     * @date 2022/3/31
     * @param MessageMapper $message
     * @return MessageMapper
     * @author litong
     */
    protected function messageFormat(MessageMapper $message) {
        $message->uniqId = is_null($message->uniqId) ? uniqid() : $message->uniqId;
        $message->topic = is_null($message->topic) ? "public" : $message->topic;
        if (stripos($message->topic, '_') !== false) {
            throw new \Exception("topic " . $message->topic . " 错误, 不能包含下划线");
        }
        $message->duplicateSecond = (is_numeric($message->duplicateSecond) && $message->duplicateSecond > 0) ? intval($message->duplicateSecond) : null;
        return $message;
    }

    /**
     * 格式化 发送体
     * @date 2022/3/31
     * @param SenderMapper $sender
     * @return SenderMapper
     * @author litong
     */
    protected function senderFormat(SenderMapper $sender) {
        return $sender;
    }

    /**
     * 消息保存至队列
     * @date 2022/3/31
     * @param MessageMapper $message
     * @return bool
     * @author litong
     */
    protected function messageToList(MessageMapper $message) {
        $this->redisHandler->zAdd($this->messageListKey, $message->sendTime ? strtotime($message->sendTime) : time(), $this->getMessagePointer($message));
        return true;
    }

    /**
     * 消息体保存至存储
     * @date 2022/3/31
     * @param MessageSingleMapper|MessageGroupMapper $message
     * @param SenderMapper|null $sender
     * @return bool
     * @author litong
     */
    protected function saveMessageBody($redisKey, $message, SenderMapper $sender = null) {
        $data = json_encode(["message" => $message, "sender" => $sender, "sender_class" => get_class($sender)]);
        if ($this->redisHandler->hSet($redisKey, $this->getMessageId($message), $data) !== false) {
            return true;
        } else {
            self::setError(10101, __CLASS__ . " error");
            return false;
        }
    }

    /**
     * 消费消息数据
     * @date 2022/3/31
     * @return array
     * @author litong
     */
    protected function popData() {
        $time = time();
        $data = $this->redisHandler->zRangeByScore($this->messageListKey, 0, $time);
        $this->redisHandler->zRemRangeByScore($this->messageListKey, 0, $time);
        $return = [MessageSingleMapper::MESSAGE_TYPE => [], MessageGroupMapper::MESSAGE_TYPE => []];
        foreach ($data as $pointer) {
            $type = $this->pointerToType($pointer);
            if ($type === MessageSingleMapper::MESSAGE_TYPE) {
                $return[MessageSingleMapper::MESSAGE_TYPE][] = $this->popSingleData($pointer);
            } else {
                $return[MessageGroupMapper::MESSAGE_TYPE][] = $this->popGroupData($pointer);
            }
        }
        return $return;
    }

    /**
     * 消费群组消息
     * @date 2022/3/31
     * @param $pointer
     * @return array
     * @author litong
     */
    protected function popGroupData($pointer) {
        $info = $this->pointerDecode($pointer);
        $bodies = $this->redisHandler->hGetAll($this->groupDataKey($info['topic']));
        ksort($bodies);
        $this->redisHandler->del($this->groupDataKey($info['topic']));
        $groupMappers = $senderMappers = [];
        foreach ($bodies as $body) {
            $data = json_decode($body, true);
            $groupMapper = new MessageGroupMapper($data["message"]);
            $groupMappers[] = $groupMapper;
            $senderMappers[] = $this->dataToSenderMapper($data["sender_class"], $data["sender"]);
        }
        return [$groupMappers, $senderMappers];
    }

    /**
     * 消费独立消息
     * @date 2022/3/31
     * @param $messageId
     * @return array
     * @author litong
     */
    protected function popSingleData($messageId) {
        $body = $this->redisHandler->hGet($this->singleDataKey, $messageId);
        $this->redisHandler->hDel($this->singleDataKey, $messageId);
        $data = json_decode($body, true);
        $singleMapper = new MessageSingleMapper($data["message"]);
        $senderMapper = $this->dataToSenderMapper($data["sender_class"], $data["sender"]);
        return [$singleMapper, $senderMapper];
    }

    protected function dataToSenderMapper($senderClass, $sender = null) {
        return new $senderClass($sender);
    }

    /**
     * 重复消息校验
     * @date 2022/3/31
     * @param MessageMapper $message
     * @return bool
     * @author litong
     */
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
                self::setError(10102, __CLASS__ . " error ");
                return false;
            }
        }
    }

}