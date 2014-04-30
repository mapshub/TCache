<?php

namespace TCache;


use TCache\Criterias\Criteria;

class Criterias
{
    /** @var  TCache */
    private $cache;
    private $loaded = false;
    /** @var Criteria[] */
    private $list = [];

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    private function load()
    {
        if ($this->loaded === false) {
            foreach ($this->cache->getStorage()->getCriteriasList() as $nextCriteria) {
                $this->list[$nextCriteria['sid']] = new Criteria($nextCriteria['sid'], $nextCriteria['class'], $this->cache);
            }
            $this->loaded = true;
        }
    }

    /**
     * @param $criteria_id
     * @param string $WarmupperClass
     * @return Criteria
     */
    public function add($criteria_id, $WarmupperClass = "default")
    {
        $this->load();
        if (!isset($this->list[$criteria_id])) {
            $criteria = $this->cache->getStorage()->createCriteria($criteria_id, $WarmupperClass);
            $this->list[$criteria['sid']] = new Criteria($criteria['sid'], $criteria['class'], $this->cache);
        }
        return $this->list[$criteria_id];
    }

    public function getAll()
    {
        $this->load();
        return $this->list;
    }

    public function get($criteria_id)
    {
        $this->load();
        return isset($this->list[$criteria_id]) ? $this->list[$criteria_id] : null;
    }

    public function drop($criteria_id)
    {
        $this->load();
        if (isset($this->list[$criteria_id])) {
            $this->list[$criteria_id]->getValues()->dropAll();
            unset($this->list[$criteria_id]);
            $this->cache->getStorage()->dropCriteria($criteria_id);
        }
    }

    public function dropAll()
    {
        $this->load();
        foreach ($this->list as $nextCriteria) {
            $this->drop($nextCriteria->getSid());
        }
    }
}