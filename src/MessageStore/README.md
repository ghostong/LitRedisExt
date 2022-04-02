# 信息整合器

### 初始化Redis

也可以使项目中已初始化好的redisHandler

````php
//连接redis
$redisHandler = new \Redis();
$redisHandler->connect("192.168.1.163");
\Lit\RedisExt\MessageStore::init($redisHandler);
````

````php
$accessToken = "42cb75c8cb3092ee3bb312e1149256defb104c305e7fd06b78adef02d6e776da";
$token = "SEC3fc93ae678c453dbae5ee813d8f7be3c75ffdc4d5917d79a728c948bfb8164b7";
````

### 使用 钉钉发送单条消息

````php
//配置消息发送参数
$message = new \Lit\RedisExt\MessageStore\Mapper\MessageSingleMapper();
$message->topic = "test";
$message->uniqId = uniqid();
$message->body = "测试消息 十秒钟后发 " . date("Y-m-d H:i:s");
$message->sendTime = date("Y-m-d H:i:s", time() + 10);
$message->duplicateSecond = 60;

//发送钉钉Text消息
$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingTextMapper();
$dingSender->token = $token;
$dingSender->accessToken = $accessToken;

\Lit\RedisExt\MessageStore::single()->setMessage($message)->setSender($dingSender)->send();
\Lit\RedisExt\MessageStore::single()->setMessage($message)->send();
````

### 使用 钉钉发送群组消息

````php

//配置消息发送参数
$message = new \Lit\RedisExt\MessageStore\Mapper\MessageGroupMapper();
$message->topic = "test";
$message->title = "测试消息" . uniqid();
$message->body = "#### 标题啊标题\n > Markdown 啊 Markdown \n\n > ###### 延时20秒 [点击一下](https://www.php.net/) \n";
$message->uniqId = uniqid();
$message->sendTime = date("Y-m-d H:i:s", time() + 5);
$message->duplicateSecond = 60;

//钉钉发送Markdown消息
$dingSender = new \Lit\RedisExt\MessageStore\Mapper\SenderDingMarkdownMapper();
$dingSender->accessToken = $accessToken;
$dingSender->token = $token;

//第一条消息
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->setSender($dingSender)->send();
$message->sendTime = date("Y-m-d H:i:s", time() + 10);

//第二条消息
$message->uniqId = uniqid();
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->setSender($dingSender)->send();

//第三条消息
$message->sendTime = date("Y-m-d H:i:s", time() + 15);
$message->uniqId = uniqid();
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->setSender($dingSender)->send();
$messageSender = \Lit\RedisExt\MessageStore::group()->setMessage($message)->send();
````

### 自动发送信息

````php
\Lit\RedisExt\MessageStore::autoRun();
````

### 使用自定义的方式发送

````php
\Lit\RedisExt\MessageStore::run(
    //单条消息发送
    function (MessageMapper $message, SenderMapper $sender = null) {
        var_dump($message, $sender);
    },
    //群组消息发送
    function (array $messages, array $senders = null) {
        foreach ($messages as $messageGroupMapper) {
            /** @var MessageGroupMapper $messageGroupMapper */
            var_dump($messageGroupMapper);
        }
        foreach ($senders as $senderMapper) {
            /** @var SenderMapper $messageGroupMapper */
            var_dump($messageGroupMapper);
        }
    }
);
````