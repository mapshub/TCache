<?php

namespace TCache;

class Jobs
{

    const CREATE_CRITERIA = "create_criteria";
    const DROP_CRITERIA = "drop_criteria";
    const REBUILD_ITEM = "rebuild_item";
    const REBUILD_VALUES = "rebuild_values";

    /** @var  TCache */
    private $cache;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    public function add($key, $attr)
    {
        $this->cache->getStorage()->addJob($key, $attr);
    }

    public function makeNext()
    {
        $this->cache->getStorage()->makeJob();
    }

    public function makeAll()
    {
        while ($this->cache->getStorage()->countJobs() > 0) {
            $this->makeNext();
        }
    }

    public function dropAll()
    {
        $this->cache->getStorage()->dropJobs();
    }
} 