<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingFeedCardMapper extends SenderDingMapper
{

    const MSG_TYPE = "feedCard";

    public $links = [
        [
            "title" => "",
            "messageURL" => "",
            "picURL" => ""
        ]
    ];

}