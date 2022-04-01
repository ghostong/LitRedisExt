<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingMapper extends SenderMapper
{

    const SENDER_TYPE = "ding";

    public $accessToken = null;

    public $token = null;

}