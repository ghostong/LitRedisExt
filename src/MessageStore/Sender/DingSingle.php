<?php

namespace Lit\RedisExt\MessageStore\Sender;

use Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingTextMapper;

class DingSingle extends Ding
{
    public static function text(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = __FUNCTION__;
        $data["text"]["content"] = $message->body;
        if (property_exists($sender, "atMobiles")) {
            $data["at"]["atMobiles"] = $sender->atMobiles;
        }
        if (property_exists($sender, "atUserIds")) {
            $data["at"]["atUserIds"] = $sender->atUserIds;
        }
        if (property_exists($sender, "isAtAll")) {
            $data["at"]["isAtAll"] = $sender->isAtAll;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function markdown(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = __FUNCTION__;
        $data["markdown"]["title"] = $message->title;
        $data["markdown"]["text"] = $message->body;
        if (property_exists($sender, "atMobiles")) {
            $data["at"]["atMobiles"] = $sender->atMobiles;
        }
        if (property_exists($sender, "atUserIds")) {
            $data["at"]["atUserIds"] = $sender->atUserIds;
        }
        if (property_exists($sender, "isAtAll")) {
            $data["at"]["isAtAll"] = $sender->isAtAll;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function link(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = __FUNCTION__;
        $data["link"]["text"] = $message->body;
        $data["link"]["title"] = $message->title;
        if (property_exists($sender, "picUrl")) {
            $data["link"]["picUrl"] = $sender->picUrl;
        }
        if (property_exists($sender, "messageUrl")) {
            $data["link"]["messageUrl"] = $sender->messageUrl;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function feedCard(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = "text";
        $data["text"]["content"] = $message->body;
        self::request($data, $sender->accessToken, $sender->token);

        $data["msgtype"] = __FUNCTION__;
        if (property_exists($sender, "links")) {
            $data["feedCard"]["links"] = $sender->links;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function actionCard(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = __FUNCTION__;
        $data["actionCard"]["title"] = $message->title;
        $data["actionCard"]["text"] = $message->body;

        if (property_exists($sender, "links")) {
            $data["actionCard"]["btnOrientation"] = $sender->links;
        }
        if (property_exists($sender, "btns")) {
            $data["actionCard"]["btns"] = $sender->btns;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

}