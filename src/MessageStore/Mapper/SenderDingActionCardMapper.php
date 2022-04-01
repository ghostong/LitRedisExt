<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingActionCardMapper extends SenderDingMapper
{

    const MSG_TYPE = "actionCard";

    public $btnOrientation = 0;

    public $btns = [
        [
            "title" => "",
            "actionURL" => ""
        ]
    ];
}