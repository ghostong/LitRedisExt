<?php


namespace Lit\RedisExt\Structs;


class CacheSupGetKey
{
    //redis key
    protected $key = "";
    //空结果回调函数参数
    protected $params = [];

    public function __construct($key, $params) {
        $this->key = $key;
        $this->params = $params;
    }

    public function getKey() {
        return $this->key;
    }

    public function getParams() {
        return $this->params;
    }

}