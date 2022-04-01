<?php

namespace Lit\RedisExt\MessageStore\Sender;

use Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper;
use Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingTextMapper;

class DingGroup extends Ding
{
    public static function text(array $messages, array $senders) {
        $sender = $senders[count($senders) - 1];
        $data["msgtype"] = constant(get_class($sender) . "::MSG_TYPE");
        $content = "";
        $atMobiles = $atUserIds = $isAtAll = [];
        foreach ($messages as $key => $messageGroupMapper) {
            /** @var MessageGroupMapper $messageGroupMapper */
            $content .= $messageGroupMapper->title . "\n" . $messageGroupMapper->body . "\n\n";
            $sender = $senders[$key];
            if (property_exists($sender, "atMobiles")) {
                $atMobiles = array_merge($atMobiles, $sender->atMobiles);
            }
            if (property_exists($sender, "atUserIds")) {
                $atUserIds = array_merge($atUserIds, $sender->atUserIds);
            }
            if (property_exists($sender, "isAtAll")) {
                $isAtAll = array_merge($isAtAll, [$sender->isAtAll]);
            }
        }
        $data["at"]["atMobiles"] = $atMobiles;
        $data["at"]["atUserIds"] = $atUserIds;
        $data["at"]["isAtAll"] = array_sum($isAtAll) > 0;
        $data["text"]["content"] = trim($content, "\n");
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function markdown($messages, $senders) {
        $sender = $senders[count($senders) - 1];
        $data["msgtype"] = constant(get_class($sender) . "::MSG_TYPE");
        $title = $content = "";
        $atMobiles = $atUserIds = $isAtAll = [];
        foreach ($messages as $key => $messageGroupMapper) {
            /** @var MessageGroupMapper $messageGroupMapper */
            $content .= $messageGroupMapper->title . "\n" . $messageGroupMapper->body . "\n\n";
            $sender = $senders[$key];
            if (property_exists($sender, "atMobiles")) {
                $atMobiles = array_merge($atMobiles, $sender->atMobiles);
            }
            if (property_exists($sender, "atUserIds")) {
                $atUserIds = array_merge($atUserIds, $sender->atUserIds);
            }
            if (property_exists($sender, "isAtAll")) {
                $isAtAll = array_merge($isAtAll, [$sender->isAtAll]);
            }
            $title = $messageGroupMapper->title;
        }
        $data["at"]["atMobiles"] = $atMobiles;
        $data["at"]["atUserIds"] = $atUserIds;
        $data["at"]["isAtAll"] = array_sum($isAtAll) > 0;
        $data["markdown"]["text"] = trim($content, "\n");
        $data["markdown"]["title"] = $title;
        self::request($data, $sender->accessToken, $sender->token);

    }

    public static function link(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = constant(get_class($sender) . "::MSG_TYPE");
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
        $data["msgtype"] = SenderDingTextMapper::MSG_TYPE;
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

        $data["msgtype"] = constant(get_class($sender) . "::MSG_TYPE");
        if (property_exists($sender, "links")) {
            $data["feedCard"]["links"] = $sender->links;
        }
        self::request($data, $sender->accessToken, $sender->token);
    }

    public static function actionCard(MessageSingleMapper $message, SenderDingMapper $sender) {
        $data["msgtype"] = constant(get_class($sender) . "::MSG_TYPE");
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