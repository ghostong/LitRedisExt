<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingMarkdownMapper extends SenderDingMapper
{

    const MSG_TYPE = "markdown";

    public $atMobiles = [];

    public $atUserIds = [];

    public $isAtAll = false;

}