<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingLinkMapper extends SenderDingMapper
{

    const MSG_TYPE = "link";

    public $picUrl = null;

    public $messageUrl = null;

}