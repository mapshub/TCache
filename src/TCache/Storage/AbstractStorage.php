<?php

namespace TCache\Storage;

use TCache\TCache;

abstract class AbstractStorage
{
    /**
     * @param TCache $cache
     * @return mixed
     */
    abstract public function setCache($cache);

    /**
     * @return TCache
     */
    abstract public function getCache();

    /**
     * @return []
     */
    abstract public function getCriteriasList();

    /** @ret ['sid', 'class'] */
    abstract public function createCriteria($id, $warmupClass);

    abstract public function dropCriteria($id);

    abstract public function getValuesList($criteria_id);

    abstract public function getValuesAggregation($criteria_id, $query = [], $ids = []);

    /** @ret ['criteria_id', 'sid', 'text'] */
    abstract public function createValue($criteria_id, $value_sid, $value_text);

    abstract public function dropValue($criteria_id, $value_sid);

    abstract public function addItem($item_id, $attributes);

    abstract public function findItems($values = [], $ids = []);

    abstract public function dropItems($values = [], $ids = []);

    abstract public function countItems($values = [], $ids = []);

    abstract public function addJob($id, $attr);

    abstract public function makeJob();

    abstract public function countJobs();

    abstract public function dropJobs();

}