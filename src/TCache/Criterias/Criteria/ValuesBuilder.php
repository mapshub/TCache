<?php

namespace TCache\Criterias\Criteria;


use TCache\Criterias\Criteria;

class ValuesBuilder
{
    /** @var  Criteria */
    private $criteria;
    private $numconverter = null;

    function __construct($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return \NumberFormatter
     */
    private function getNumconverter()
    {
        if (is_null($this->numconverter)) {
            $this->numconverter = \NumberFormatter::create('ru', \NumberFormatter::DECIMAL);
            $this->numconverter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $this->numconverter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 100);
        }

        return $this->numconverter;
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
            $value = ['id' => $this->castValueSid($item[$value_key]), 'text' => $this->castValueText($item[$text_key])];
        } else {
            throw new \Exception("item value key '{$sid}' not found!");
        }

        return $value;
    }

    public function castValueSid($id)
    {
        //echo "(" . gettype($id) . ")" . $id . "=>";
        if (gettype($id) == "double") {
            $id = $this->getNumconverter()->format($id);
        } else {
            settype($id, "string");
        }
        //echo "(" . gettype($id) . ")" . $id . "\n";
        return $id;
    }

    public function castValueText($text)
    {
        settype($text, "string");
        return $text;
    }
} 