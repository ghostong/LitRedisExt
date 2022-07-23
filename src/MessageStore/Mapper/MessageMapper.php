<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class MessageMapper extends BaseMapper
{

    /**
     * 消息类型  不能包含下划线(_)
     * @example 字符串
     */
    public $topic = null;

    /**
     * 消息标题  字符串消息
     * @example 字符串
     */
    public $title = null;

    /**
     * 消息体  字符串消息
     * @example 字符串
     */
    public $body = null;

    /**
     * 发送时间  如果配置此项,即定时发送此消息, 不配置或者配置的时间已经过期为立即发送
     * @example 2021-01-01 01:01:01
     */
    public $sendTime = null;

    /**
     * 唯一ID  如果配置此项, uniqId重复的消息将被覆盖, 不配置所有的消息将被保留 定时(立即)发送
     * @example uniqid()
     */
    public $uniqId = null;

    /**
     * 同一个消息的冷却时间 (单位: 秒), 计时时间内消息重复不会被发送
     * @example 86400
     */
    public $duplicateSecond = null;


}