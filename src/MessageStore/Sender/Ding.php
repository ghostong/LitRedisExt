<?php

namespace Lit\RedisExt\MessageStore\Sender;

use Lit\RedisExt\MessageStore\ErrorMsg;

class Ding extends ErrorMsg
{
    protected static function request($postData, $accessToken, $token = null) {
        $url = "https://oapi.dingtalk.com/robot/send?access_token=" . $accessToken;
        $url = $url . self::getToken($token);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result, true);
        if (is_array($data) && isset($data['errcode']) && isset($data['errcode']) == 0) {
            return true;
        } else {
            self::setError($data['errcode'], $data['errmsg']);
            return false;
        }
    }

    private static function getToken($token) {
        if (!is_null($token)) {
            $timestamp = time() * 1000;
            $sign = hash_hmac("SHA256", $timestamp . "\n" . $token, $token, true);
            $sign = base64_encode($sign);
            $sign = urlencode($sign);
            return "&timestamp=" . $timestamp . "&sign=" . $sign;
        } else {
            return "";
        }
    }

}