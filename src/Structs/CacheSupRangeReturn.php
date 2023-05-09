<?php


namespace Lit\RedisExt\Structs;


class CacheSupRangeReturn
{
    public $data;
    public $cursor;
    public $endScore;
    public $total;
    public $limit;

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getCursor() {
        return $this->cursor;
    }

    /**
     * @return int
     */
    public function getEndScore() {
        return $this->endScore;
    }

    /**
     * @return int
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }


}