<?php

namespace TCache;

use TCache\Criterias\Criteria;

class Items
{

    /** @var  TCache */
    private $cache;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \TCache\TCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function add($id, $attributes)
    {
        foreach ($this->getCache()->getCriterias()->getAll() as $nextCriteria) {
            $arrValue = $nextCriteria->getValuesBuilder()->getValueByItem($attributes);
            if (!is_null($arrValue)) {
                $nextCriteria->getValues()->add($arrValue['id'], $arrValue['text']);
            }
        }
        $this->getCache()->getStorage()->addItem($id, $attributes);
    }

    public function get($id)
    {
        $item = null;
        foreach ($this->find([], [$id]) as $next) {
            $item = $next;
        }
        return $item;
    }

    public function find($values = [], $ids = [])
    {
        return $this->getCache()->getStorage()->findItems($values, $ids);
    }

    public function drop($values = [], $ids = [])
    {
        $this->getCache()->getStorage()->dropItems($values, $ids);
    }

    public function count($values = [], $ids = [])
    {
        return $this->getCache()->getStorage()->countItems($values, $ids);
    }

}