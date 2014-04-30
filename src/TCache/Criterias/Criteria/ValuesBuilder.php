<?php

namespace TCache\Criterias\Criteria;


use TCache\Criterias\Criteria;

class ValuesBuilder
{
    /** @var  Criteria */
    private $criteria;

    function __construct($criteria)
    {
        $this->criteria = $criteria;
    }

    public function getValueByItem($item)
    {
        $sid = $this->criteria->getSid();
        $value_key = null;
        $text_key = null;
        $value = null;

        if (isset($item[$sid])) {
            $value_key = $sid;
        }

        if (isset($item[$sid . "_id"])) {
            $value_key = $sid . "_id";
        }

        if (!is_null($value_key)) {
            if (isset($item[$sid . "_text"])) {
                $text_key = $sid . "_text";
            } else {
                $text_key = $value_key;
            }
        }


        if (!is_null($value_key) && !is_null($text_key)) {
            $value = ['id' => $item[$value_key], 'text' => $item[$text_key]];
        }

        return $value;
    }
} 