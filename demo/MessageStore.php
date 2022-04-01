<?php

use Lit\RedisExt\Mapper\GroupMessageMapper;
use Lit\RedisExt\Mapper\SingleMessageMapper;

include(dirname(__DIR__) . "/vendor/autoload.php");

//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.25");
\Lit\RedisExt\MessageStore::init($redisHandler);

//$message = new \Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper();
//$message->topic = "test";
//$message->uniqId = uniqid();
////$message->uniqId = 111;
//$message->body = "#### 杭州天气 @156xxxx8827\n" .
//    "> 9度，西北风1级，空气良89，相对温度73%\n\n" .
//    "> ![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png)\n" .
//    "> ###### 10点20分发布 [天气](http://www.thinkpage.cn/) \n";
//$message->title = "markdown";
//$message->sendTime = date("Y-m-d H:i:s", time());
//$message->duplicateSecond = 60;

//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingTextMapper();

//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingMarkdownMapper();

//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingLinkMapper();
//$dingSender->messageUrl = "https://www.baidu.com";
//$dingSender->picUrl = "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fpic.jj20.com%2Fup%2Fallimg%2F911%2F050916125K7%2F160509125K7-11.jpg&refer=http%3A%2F%2Fpic.jj20.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1651393122&t=08134498cda33160b0759509aabc8c44";

//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingFeedCardMapper();
//$dingSender->links = [
//    [
//        "title" => "时代的火车向前开1",
//        "messageURL" => "https=>//www.dingtalk.com/",
//        "picURL" => "https=>//img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png"
//    ],
//    [
//        "title" => "时代的火车向前开2",
//        "messageURL" => "https=>//www.dingtalk.com/",
//        "picURL" => "https=>//img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png"
//    ]
//];

//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingActionCardMapper();
//
//$dingSender->btnOrientation = 1;
//$dingSender->btns = [
//    ["title" => "内容不错",
//        "actionURL" => "https://www.dingtalk.com/"
//    ],
//    [
//        "title" => "不感兴趣",
//        "actionURL" => "https://www.dingtalk.com/"
//    ]
//];

//$dingSender->accessToken = "42cb75c8cb3092ee3bb312e1149256defb104c305e7fd06b78adef02d6e776da";
//$dingSender->token = "SEC3fc93ae678c453dbae5ee813d8f7be3c75ffdc4d5917d79a728c948bfb8164b7";
//$dingSender->atMobiles = ["+86-15652627052"];
//$dingSender->atUserIds = ["vnvpokm"];
//$dingSender->isAtAll = true;
//\Lit\RedisExt\MessageStore::single()->setMessage($message)->setSender($dingSender)->send();


$message = new \Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper();
$message->topic = "test";
$message->title = "title" . uniqid();
$message->uniqId = uniqid();
//$message->uniqId = 222;
$message->body = "测试Message" . uniqid();
$message->sendTime = date("Y-m-d H:i:s", time());
//$message->duplicateSecond = 60;
//$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingTextMapper();
$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingMarkdownMapper();
$message->body = "#### 杭州天气 @156xxxx8827\n" .
    "> 9度，西北风1级，空气良89，相对温度73%\n\n" .
    "> ![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png)\n" .
    "> ###### 10点20分发布 [天气](http://www.thinkpage.cn/) \n";

$dingSender->accessToken = "42cb75c8cb3092ee3bb312e1149256defb104c305e7fd06b78adef02d6e776da";
$dingSender->token = "SEC3fc93ae678c453dbae5ee813d8f7be3c75ffdc4d5917d79a728c948bfb8164b7";

$dingSender->atMobiles = ["+86-12345678911"];
//$dingSender->isAtAll = true;

$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->setSender($dingSender)->send();
$message->uniqId = uniqid();
$dingSender->atMobiles = ["+86-15652627052"];
$message->title = "title" . uniqid();
$message->body = "#### 杭州天气 @156xxxx8827\n" .
    "> 9度，西北风1级，空气良89，相对温度73%\n\n" .
    "> ![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png)\n" .
    "> ###### 10点20分发布 [天气](http://www.thinkpage.cn/) \n";
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->setSender($dingSender)->send();

//var_dump($messageSender->getErrorCode(), $messageSender->getErrorMessage());

//\Lit\RedisExt\MessageStore::run(function (array $message, $sender) {
//    var_dump(111, $message, $sender);
//}, function (array $message, $sender) {
//    var_dump(222, $message, $sender);
//});

\Lit\RedisExt\MessageStore::autoRun();