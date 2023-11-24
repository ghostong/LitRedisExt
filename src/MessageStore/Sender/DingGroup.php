<?php

namespace Lit\RedisExt\MessageStore\Sender;

use Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper;
use Lit\RedisExt\MessageStore\Mapper\SenderDingMapper;

class DingGroup extends Ding
{

    const MARKDOWN_MAX_LENGTH = 5000;

    public static function text(array $messages, array $senders) {
        $data["msgtype"] = __FUNCTION__;
        $content = "";
        $atMobiles = $atUserIds = $isAtAll = [];
        foreach ($messages as $key => $messageGroupMapper) {
            /** @var MessageGroupMapper $messageGroupMapper */
            $content .= $messageGroupMapper->title . "\n" . $messageGroupMapper->body . "\n\n";
            /** @var SenderDingMapper $senderDingMapper */
            $senderDingMapper = $senders[$key];
            if (property_exists($senderDingMapper, "atMobiles")) {
                $atMobiles = array_merge($atMobiles, $senderDingMapper->atMobiles);
            }
            if (property_exists($senderDingMapper, "atUserIds")) {
                $atUserIds = array_merge($atUserIds, $senderDingMapper->atUserIds);
            }
            if (property_exists($senderDingMapper, "isAtAll")) {
                $isAtAll = array_merge($isAtAll, [$senderDingMapper->isAtAll]);
            }
        }
        $data["at"]["atMobiles"] = $atMobiles;
        $data["at"]["atUserIds"] = $atUserIds;
        $data["at"]["isAtAll"] = array_sum($isAtAll) > 0;
        $data["text"]["content"] = trim($content, "\n");
        /** @var SenderDingMapper $senderDingMapper */
        $senderDingMapper = $senders[count($senders) - 1];
        return self::request($data, $senderDingMapper->accessToken, $senderDingMapper->token);
    }

    public static function markdown($messages, $senders) {
        $data["msgtype"] = __FUNCTION__;
        $title = $content = "";
        $atMobiles = $atUserIds = $isAtAll = [];
        /** @var MessageGroupMapper $messageGroupMapper */
        /** @var SenderDingMapper $senderDingMapper */
        foreach ($messages as $key => $messageGroupMapper) {
            $senderDingMapper = $senders[$key];
            if (property_exists($senderDingMapper, "atMobiles")) {
                $atMobiles = array_unique(array_merge($atMobiles, $senderDingMapper->atMobiles));
            }
            if (property_exists($senderDingMapper, "atUserIds")) {
                $atUserIds = array_unique(array_merge($atUserIds, $senderDingMapper->atUserIds));
            }
            if (property_exists($senderDingMapper, "isAtAll")) {
                $isAtAll = array_merge($isAtAll, [$senderDingMapper->isAtAll]);
            }
            $title = $messageGroupMapper->title;
            $contentMobiles = (!empty($atMobiles)) ? (' @' . implode(' ,@', $atMobiles)) : '';
            $tmpContent = $messageGroupMapper->title . "\n" . $messageGroupMapper->body . "\n" . $contentMobiles . "\n\n";
            if (strlen($content) > 0 && strlen($content) + strlen($tmpContent) > self::MARKDOWN_MAX_LENGTH) {
                self::send($data, $senders, $atMobiles, $atUserIds, $isAtAll, $content, $title);
                $atMobiles = $atUserIds = $isAtAll = [];
                $content = $tmpContent;
            } else {
                $content .= $tmpContent;
            }
        }
        if (strlen($content) > 0) {
            self::send($data, $senders, $atMobiles, $atUserIds, $isAtAll, $content, $title);
        }

    }

    public static function link($messages, $senders) {
        $data["msgtype"] = __FUNCTION__;

    }

    public static function feedCard($message, $sender) {
        $data["msgtype"] = __FUNCTION__;

    }

    public static function actionCard($message, $sender) {
        $data["msgtype"] = __FUNCTION__;

    }

    protected static function send($data, $senders, $atMobiles, $atUserIds, $isAtAll, $content, $title) {
        $data["at"]["atMobiles"] = $atMobiles;
        $data["at"]["atUserIds"] = $atUserIds;
        $data["at"]["isAtAll"] = array_sum($isAtAll) > 0;
        $data["markdown"]["text"] = trim($content, "\n");
        $data["markdown"]["title"] = $title;
        /** @var SenderDingMapper $senderDingMapper */
        $senderDingMapper = $senders[count($senders) - 1];
        return self::request($data, $senderDingMapper->accessToken, $senderDingMapper->token);
    }

}