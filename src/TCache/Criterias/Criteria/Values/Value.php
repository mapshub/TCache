<?php

namespace TCache\Criterias\Criteria\Values;

use TCache\Criterias\Criteria;

class Value
{
    /** @var  Criteria */
    private $criteria;
    private $sid;
    private $text;

    function __construct($sid, $text, $criteria)
    {
        $this->sid = $sid;
        $this->text = $text;
        $this->criteria = $criteria;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function getText()
    {
        return $this->text;
    }

    /**
     * @param Value[] $arrValues
     */
    public function getItems($arrValues = [])
    {
        $qr = [];
        foreach ($arrValues as $value) {
            if ($value->criteria->getSid() != $this->criteria->getSid()) {
                $qr[] = $value;
            }
        }
        $qr[] = $this;
        return $this->criteria->getCache()->getStorage()->findItems($qr);
    }

    /**
     * @param Value[] $arrValues
     */
    public function getCount($arrValues = [])
    {
        $qr = [];
        foreach ($arrValues as $value) {
            if ($value->criteria->getSid() != $this->criteria->getSid()) {
                $qr[] = $value;
            }
        }
        $qr[] = $this;
        return $this->criteria->getCache()->getStorage()->countItems($qr);
    }

    /**
     * @return \TCache\Criterias\Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}