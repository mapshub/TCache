<?php

namespace TCache;


use TCache\Storage\AbstractStorage;

class TCache
{
    private $name;
    /** @var  Criterias */
    private $criterias;
    /** @var  Items */
    private $items;
    /** @var  AbstractStorage */
    private $storage;
    private $jobs;

    function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->criterias = null;
        $this->items = null;
        $this->jobs = null;
        $this->name = $name;
        if (!is_null($this->getStorage())) {
            $this->getStorage()->setCache($this);
        }
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCriterias()
    {
        if (is_null($this->criterias)) {
            $this->criterias = new Criterias($this);
        }
        return $this->criterias;
    }

    /**
     * @param \TCache\Storage\AbstractStorage $storage
     */
    public function setStorage($storage)
    {
        $this->criterias = null;
        $this->items = null;
        $this->jobs = null;
        $storage->setCache($this);
        $this->storage = $storage;
    }

    /**
     * @return \TCache\Storage\AbstractStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return Jobs
     */
    public function getJobs()
    {
        if (is_null($this->jobs)) {
            $this->jobs = new Jobs($this);
        }
        return $this->jobs;
    }

    /**
     * @return \TCache\Items
     */
    public function getItems()
    {
        if (is_null($this->items)) {
            $this->items = new Items($this);
        }
        return $this->items;
    }

    public function dropAll()
    {
        $this->getCriterias()->dropAll();
        $this->getItems()->drop();
        $this->getJobs()->dropAll();
    }
}