<?php


namespace Lit\RedisExt\Structs;


class CacheSupRangeKey
{
    //redis key
    public $key = "";
    public $cursor = 0;
    public $limit = 10;

    public function __construct($key = "", $cursor = 0, $limit = 10) {
        $this->key = $key;
        $this->cursor = $cursor;
        $this->limit = $limit;
    }

    public function getKey() {
        return $this->key;
    }

    public function getCursor() {
        return $this->cursor;
    }

    public function getLimit() {
        return $this->limit;
    }

}