<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingTextMapper extends SenderDingMapper
{

    const MSG_TYPE = "text";

    public $atMobiles = [];

    public $atUserIds = [];

    public $isAtAll = false;

}