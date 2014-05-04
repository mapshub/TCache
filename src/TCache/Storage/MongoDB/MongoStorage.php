<?php

namespace TCache\Storage\MongoDB;

use TCache\Criterias\Criteria\Values\Value;
use TCache\Criterias\Criteria;
use TCache\Jobs;
use TCache\Storage\AbstractStorage;
use TCache\TCache;

class MongoStorage extends AbstractStorage
{
    use TMongoStorage;

    private $cache;

    /**
     * @param TCache $cache
     * @return mixed
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        $this->getDb()->TCacheCriterias->ensureIndex(['cache_name' => 1, 'sid' => 1], ["unique" => true]);
        $this->getDb()->TCacheValues->ensureIndex(['cache_name' => 1, 'criteria_id' => 1, 'sid' => 1], ["unique" => true]);
        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->ensureIndex(['id' => 1], ["unique" => true]);
        $this->getDb()->TCacheJobs->ensureIndex(['cache_name' => 1, 'job' => 1]);
    }

    /**
     * @return TCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function getCriteriasList()
    {
        return $this->getDb()->TCacheCriterias->find(['cache_name' => $this->getCache()->getName()]);
    }

    /** @ret ['sid', 'class'] */
    public function createCriteria($id, $warmupClass)
    {
        $id = (string)$id;
        /*
        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->ensureIndex([$id => 1], ['name' => $id . "_index"]);
        */
        $this->getCache()->getJobs()->add(Jobs::CREATE_CRITERIA, ['id' => $id]);
        $this->getDb()->TCacheCriterias->insert(['cache_name' => $this->getCache()->getName(), 'sid' => $id, 'class' => $warmupClass]);
        return $this->getDb()->TCacheCriterias->findOne(['cache_name' => $this->getCache()->getName(), 'sid' => $id]);
    }

    public function dropCriteria($id)
    {
        $id = (string)$id;

        /*
        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->deleteIndex($id . "_index");
        */

        $this->getDb()->TCacheCriterias->remove(['cache_name' => $this->getCache()->getName(), 'sid' => $id]);
        $this->getCache()->getJobs()->add(Jobs::DROP_CRITERIA, ['id' => $id]);
    }

    /** @ret ['criteria_id', 'sid', 'text'] */
    public function getValuesList($criteria_id)
    {
        $criteria_id = (string)$criteria_id;
        return $this->getDb()->TCacheValues->find(['cache_name' => $this->getCache()->getName(), 'criteria_id' => $criteria_id]);
    }

    public function createValue($criteria_id, $value_sid, $value_text)
    {
        $criteria_id = (string)$criteria_id;
        $value_sid = (string)$value_sid;
        $value_text = (string)$value_text;
        $this->getDb()->TCacheValues->insert(['cache_name' => $this->getCache()->getName(), 'criteria_id' => $criteria_id, 'sid' => $value_sid, 'text' => $value_text]);
        return $this->getDb()->TCacheValues->findOne(['cache_name' => $this->getCache()->getName(), 'criteria_id' => $criteria_id, 'sid' => $value_sid]);
    }

    public function dropValue($criteria_id, $value_sid)
    {
        $criteria_id = (string)$criteria_id;
        $value_sid = (string)$value_sid;
        $this->getDb()->TCacheValues->remove(['cache_name' => $this->getCache()->getName(), 'criteria_id' => $criteria_id, 'sid' => $value_sid]);
    }

    public function addItem($item_id, $attributes)
    {
        $item_id = (string)$item_id;
        $item = ['id' => $item_id, 'attr' => $attributes];

        foreach ($this->getCache()->getCriterias()->getAll() as $nextCriteria) {
            $arrValue = $nextCriteria->getValuesBuilder()->getValueByItem($attributes);
            if (!is_null($arrValue)) {
                $item[$nextCriteria->getSid()] = $arrValue['id'];
            }
        }

        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->update(['id' => $item_id], $item, ['upsert' => true]);
    }

    public function findItems($values = [], $ids = [])
    {
        $qr = $this->qrBuild($values, $ids);
        $colName = $this->getCache()->getName() . "_items";
        return $this->getDb()->selectCollection($colName)->find($qr);
    }

    public function dropItems($values = [], $ids = [])
    {
        $qr = $this->qrBuild($values, $ids);
        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->remove($qr);
    }

    public function countItems($values = [], $ids = [])
    {
        $qr = $this->qrBuild($values, $ids);
        $colName = $this->getCache()->getName() . "_items";
        return $this->getDb()->selectCollection($colName)->count($qr);
    }

    public function addJob($id, $attr)
    {
        $id = (string)$id;
        $this->getDb()->TCacheJobs->insert(['cache_name' => $this->getCache()->getName(), 'job' => $id, 'attr' => $attr]);
    }

    public function makeJob()
    {
        $job = $this->getDb()->TCacheJobs->findAndModify(['cache_name' => $this->getCache()->getName()], null, null, ["remove" => true]);
        $name = $job['job'];
        $attr = $job['attr'];
        if ($name == Jobs::CREATE_CRITERIA) {
            $this->makeCreateCriteriaJob($attr);
        }
        if ($name == Jobs::REBUILD_ITEM) {
            $this->makeRebuildItemJob($attr);
        }
        if ($name == Jobs::DROP_CRITERIA) {
            $this->makeDropCriteriaJob($attr);
        }
    }

    public function countJobs()
    {
        return $this->getDb()->TCacheJobs->count(['cache_name' => $this->getCache()->getName()]);
    }

    public function dropJobs()
    {
        $this->getDb()->TCacheJobs->remove(['cache_name' => $this->getCache()->getName()]);
    }

    /**
     * @param Value[] $values
     * @param array $ids
     * @return array
     */
    private function qrBuild($values = [], $ids = [])
    {

        $qr = [];
        if (!empty($ids)) {
            $qr['id'] = ['$in' => array_map(function ($v) {
                return (string)$v;
            }, $ids)];
        }
        if (!empty($values)) {
            $c = [];
            foreach ($values as $value) {
                $csid = $value->getCriteria()->getSid();
                if (!isset($c[$csid])) {
                    $c[$csid] = [];
                }
                $vsid = $value->getSid();
                $c[$csid][] = $vsid;
            }
            foreach ($c as $field => $item_values) {
                $tmp = null;
                if (is_array($item_values)) {
                    if (count($item_values) > 1) {
                        $tmp = ['$in' => array_map(function ($v) {
                            return (string)$v;
                        }, $item_values)];
                    } else {
                        $tmp = (string)$item_values[0];
                    }
                }
                if (!is_null($tmp)) {
                    $qr[$field] = $tmp;
                }
            }
        }
        return $qr;
    }

    private function makeCreateCriteriaJob($attr)
    {
        $criteria_id = $attr['id'];
        $colName = $this->getCache()->getName() . "_items";
        $itemsCollection = $this->getDb()->selectCollection($colName);
        $itemsCollection->ensureIndex([$criteria_id => 1], ['name' => $criteria_id . "_index"]);
        $rebuild = $itemsCollection->find([$criteria_id => ['$exists' => false]], ['id']);
        if ($rebuild->count() > 0) {
            $jobs = $this->getCache()->getJobs();
            foreach ($rebuild as $itemToRebuild) {
                $jobs->add(Jobs::REBUILD_ITEM, ['id' => $itemToRebuild['id'], 'criteria_id' => $criteria_id]);
            }
        }
    }

    private function makeRebuildItemJob($attr)
    {
        $item_id = $attr['id'];
        $criteria_id = $attr['criteria_id'];
        $colName = $this->getCache()->getName() . "_items";

        $item = $this->getDb()->selectCollection($colName)->findOne(['id' => $item_id]);
        if (!empty($item)) {
            $criteria = $this->getCache()->getCriterias()->get($criteria_id);
            $arrValue = $criteria->getValuesBuilder()->getValueByItem($item['attr']);
            if (!is_null($arrValue)) {
                $this->getDb()->selectCollection($colName)->update(['id' => $item_id], ['$set' => [$criteria->getSid() => $arrValue['id']]]);
                $criteria->getValues()->add($arrValue['id'], $arrValue['text']);
            }
        }
    }

    private function makeDropCriteriaJob($attr)
    {
        $criteria_id = $attr['id'];

        $colName = $this->getCache()->getName() . "_items";
        $this->getDb()->selectCollection($colName)->deleteIndex($criteria_id . "_index");
        $this->getDb()->selectCollection($colName)->update([], ['$unset' => [$criteria_id => 'wefwef']], ['multiple' => true]);
    }
}