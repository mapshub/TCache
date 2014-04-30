<?php

namespace TCache\Criterias\Criteria;


use TCache\Criterias\Criteria;

class Values
{
    /** @var  Criteria */
    private $criteria;

    private $loaded = false;

    /** @var Criteria\Values\Value[] */
    private $list = [];

    function __construct($criteria)
    {
        $this->criteria = $criteria;
    }

    private function load()
    {
        if ($this->loaded === false) {
            foreach ($this->getCriteria()->getCache()->getStorage()->getValuesList($this->getCriteria()->getSid()) as $nextValue) {
                $this->list[$nextValue['sid']] = new Criteria\Values\Value($nextValue['sid'], $nextValue['text'], $this->getCriteria());
            }
            $this->loaded = true;
        }
    }

    public function add($sid, $text)
    {
        $this->load();
        if (!isset($this->list[$sid])) {
            $newValue = $this->getCriteria()->getCache()->getStorage()->createValue($this->getCriteria()->getSid(), $sid, $text);
            $this->list[$sid] = new Criteria\Values\Value($newValue['sid'], $newValue['text'], $this->getCriteria());
        }
        return $this->list[$sid];
    }

    public function getAll()
    {
        $this->load();
        return $this->list;
    }

    /**
     * @param $sid
     * @return Criteria\Values\Value
     */
    public function get($sid)
    {
        $this->load();
        return isset($this->list[$sid]) ? $this->list[$sid] : null;
    }

    public function drop($sid)
    {
        $this->load();
        if (isset($this->list[$sid])) {
            unset($this->list[$sid]);
            $this->getCriteria()->getCache()->getStorage()->dropValue($this->getCriteria()->getSid(), $sid);
        }
    }

    public function dropAll()
    {
        $this->load();
        foreach ($this->list as $sid => $v) {
            $this->drop($sid);
        }
        return $this;
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}