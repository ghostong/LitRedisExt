<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class SenderDingFeedCardMapper extends SenderDingMapper
{

    const MSG_TYPE = "feedCard";

    public $atMobiles = [];

    public $atUserIds = [];

    public $isAtAll = false;

    public $links = [
        [
            "title" => "",
            "messageURL" => "",
            "picURL" => ""
        ]
    ];

}