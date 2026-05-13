<?php


namespace Lit\RedisExt\Structs;


class SqlCacheReturn
{
    public $data = [];
    public $total = 0;
    public $skip = 0;
    public $limit = 0;

    public function __construct($data = [], $total = 0, $skip = 0, $limit = 0) {
        $this->data = $data;
        $this->total = $total;
        $this->skip = $skip;
        $this->limit = $limit;
    }

    public function getData() {
        return $this->data;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getSkip() {
        return $this->skip;
    }

    public function getLimit() {
        return $this->limit;
    }
}
