<?php

namespace Lit\RedisExt\MessageStore\Mapper;

class BaseMapper
{
    function __construct($data = []) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

}