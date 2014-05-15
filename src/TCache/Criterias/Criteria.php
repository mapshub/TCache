<?php

namespace TCache\Criterias;


use TCache\Criterias\Criteria\Values;
use TCache\TCache;

class Criteria
{
    /** @var  TCache */
    private $cache;

    private $sid;
    private $valuesBuilderClass;

    private $values;
    private $valuesBuilder;

    function __construct($sid, $class, $cache)
    {
        $this->sid = $sid;
        $this->valuesBuilderClass = $class;
        $this->cache = $cache;
    }

    public function getValues()
    {
        if (is_null($this->values)) {
            $this->values = new Values($this);
        }
        return $this->values;
    }

    /**
     * @return \TCache\Criterias\Criteria\ValuesBuilder
     */
    public function getValuesBuilder()
    {
        if (is_null($this->valuesBuilder)) {
            $builderClass = $this->getValuesBuilderClass();
            $this->valuesBuilder = new $builderClass($this);
        }
        return $this->valuesBuilder;
    }

    public function setValuesBuilderClass($valuesBuilderClass)
    {
        $this->valuesBuilderClass = $valuesBuilderClass;
    }

    public function getValuesBuilderClass()
    {
        if ($this->valuesBuilderClass == "default") {
            $this->valuesBuilderClass = 'TCache\Criterias\Criteria\ValuesBuilder';
        }
        return $this->valuesBuilderClass;
    }

    /**
     * @return TCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function getSid()
    {
        return $this->sid;
    }
}