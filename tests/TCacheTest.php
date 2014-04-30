<?php

namespace TCacheTest;

use TCache\Criterias;
use TCache\Storage\MongoDB\MongoStorage;
use TCache\TCache;

class TCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return TCache
     */
    public function getCache()
    {
        $storage = new MongoStorage();
        $storage->setDbName("TCacheTest");

        $tcache = new TCache("test_2");
        $tcache->setStorage($storage);

        return $tcache;
    }

    public function testName()
    {
        $tcache = new TCache("test_1");
        $this->assertEquals("test_1", $tcache->getName());

        $tcache->setName("test");
        $this->assertEquals("test", $tcache->getName());
    }

    public function testStorage()
    {
        $storage = new MongoStorage();
        $storage->setDbName("TCacheTest");
        $this->assertInstanceOf("MongoClient", $storage->getConnection());
        $this->assertInstanceOf("MongoDB", $storage->getDb());

        $tcache = new TCache();
        $tcache->setStorage($storage);
        $this->assertEquals($storage, $tcache->getStorage());
    }

    public function testCriterias()
    {
        $tcache = new TCache("test_2");

        $storage = new MongoStorage();
        $storage->setDbName("TCacheTest");

        $tcache->setStorage($storage);
        $criterias = $tcache->getCriterias();
        $this->assertInstanceOf('TCache\Criterias', $criterias);
    }

    public function testCriteriasAdd()
    {
        $tcache = $this->getCache()->setName("test_4");

        $criterias = $tcache->getCriterias();
        $criterias->add("sex");
        $criterias->add("age");
        $list = $criterias->getAll();
        $this->assertEquals(2, count($list));
    }

    public function testCriteriaLoad()
    {
        $tcache = $this->getCache()->setName("test_5");

        $criterias = $tcache->getCriterias();
        $criterias->add("sex");
        $criterias->add("age");
        $criterias->add("name");

        $tcache = $this->getCache()->setName("test_5");

        $loaded = $tcache->getCriterias()->getAll();
        $this->assertEquals(3, count($loaded));

    }

    public function testGetCriteria()
    {
        $guertsy = $this->getCache()->setName("test_6")->getCriterias()->add("guertsy");
        $guertsy_get = $this->getCache()->setName("test_6")->getCriterias()->get("guertsy");
        $this->assertEquals($guertsy, $guertsy_get);
    }

    public function testDropCriteria()
    {
        $c = $this->getCache()->setName("test_7")->getCriterias();
        $c->add("guertsy");
        $c->add("guertsy2");
        $c->add("guertsy3");
        $this->assertEquals(3, count($this->getCache()->setName("test_7")->getCriterias()->getAll()));

        $this->getCache()->setName("test_7")->getCriterias()->drop("guertsy");
        $this->assertEquals(2, count($this->getCache()->setName("test_7")->getCriterias()->getAll()));

        $this->getCache()->setName("test_7")->getCriterias()->drop("guertsy2");
        $this->getCache()->setName("test_7")->getCriterias()->drop("guertsy3");
        $this->assertEquals(0, count($this->getCache()->setName("test_7")->getCriterias()->getAll()));
    }

    public function testGetValues()
    {
        $values = $this->getCache()->setName("test_8")->getCriterias()->add("sex")->getValues();
        $this->assertInstanceOf('TCache\Criterias\Criteria\Values', $values);
        $this->assertEquals("sex", $values->getCriteria()->getSid());
    }

    public function testCreateValue()
    {
        $values = $this->getCache()->setName("test_9")->getCriterias()->add("sex")->getValues()->dropAll();
        $this->assertEquals(0, count($values->getAll()));
        $male = $values->add("M", "Male");
        $female = $values->add("F", "Female");

        $this->assertEquals(2, count($this->getCache()->setName("test_9")->getCriterias()->add("sex")->getValues()->getAll()));
        $values = $this->getCache()->setName("test_9")->getCriterias()->add("sex")->getValues();
        $this->assertEquals($male, $values->get('M'));
        $this->assertEquals($female, $values->get('F'));
    }

    public function testDropValues()
    {
        $values = $this->getCache()->setName("test_10")->getCriterias()->add("sex")->getValues()->dropAll();
        $values->add("unknown", "unknown");
        $this->assertEquals(1, count($values->getAll()));
        $values->drop("unknown");

        $values = $this->getCache()->setName("test_10")->getCriterias()->add("sex")->getValues();
        $this->assertEquals(0, count($values->getAll()));
    }

    public function testItems()
    {
        $cache = $this->getCache()->setName("test_11");
        $items = $cache->getItems();
        $this->assertInstanceOf('TCache\Items', $items);

        $items->add('m101', ['sex' => 'M', 'name' => 'Jo']);
        $items->add('m102', ['sex' => 'M', 'name' => 'Li']);
        $items->add('m103', ['sex' => 'F', 'name' => 'Mae']);

        $cache = $this->getCache()->setName("test_11");
        $items = $cache->getItems();
        $this->assertEquals(3, $items->count());

    }

    public function testReplaceItems()
    {
        $cache = $this->getCache()->setName("test_12");
        $items = $cache->getItems();
        $this->assertInstanceOf('TCache\Items', $items);

        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Jo']);
        $this->assertEquals('Jo', $items->get('m101')['attr']['name']);

        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Li']);
        $this->assertEquals('Li', $items->get('m101')['attr']['name']);

        $items->add('m101', ['sex' => 'F', 'sex_text' => 'Female', 'name' => 'Mae']);
        $this->assertEquals('Mae', $items->get('m101')['attr']['name']);
    }

    public function testBuildValuesByItems()
    {
        $cache = $this->getCache()->setName("test_13");
        $items = $cache->getItems();
        $values = $cache->getCriterias()->add("sex")->getValues()->dropAll();
        $this->assertEquals(0, count($values->getAll()));
        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Jo']);
        $items->add('m102', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Li']);
        $items->add('m103', ['sex' => 'F', 'sex_text' => 'Female', 'name' => 'Mae']);

        $values = $cache->getCriterias()->add("sex")->getValues();
        $this->assertEquals("Male", $values->get("M")->getText());
        $this->assertEquals("Female", $values->get("F")->getText());
        $this->assertEquals(2, count($values->getAll()));
    }

    public function testGetItemsByValues()
    {
        $cache = $this->getCache()->setName("test_15");
        $items = $cache->getItems();
        $cache->getCriterias()->add("country")->getValues()->dropAll();
        $cache->getCriterias()->add("sex")->getValues()->dropAll();
        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Jo', 'country' => 'china']);
        $items->add('m102', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Li', 'country' => 'china']);
        $items->add('m103', ['sex' => 'F', 'sex_text' => 'Female', 'name' => 'Mae', 'country' => 'hong kong']);

        $sexes = $cache->getCriterias()->add("sex")->getValues();
        $males = $sexes->get("M");
        $females = $sexes->get("F");
        $countries = $cache->getCriterias()->add("country")->getValues();
        $china = $countries->get("china");
        $hongkong = $countries->get("hong kong");

        $this->assertEquals(2, $males->getCount());
        $this->assertEquals(0, $males->getCount([$hongkong]));

        $this->assertEquals(1, $hongkong->getCount());
        $this->assertEquals(0, $hongkong->getCount([$males]));

        $this->assertEquals(0, $items->count([$females, $china]));
        $this->assertEquals(2, $items->count([$males, $china]));

        $items = $this->getCache()->setName("test_15")->getItems();
        $items->drop([$china, $males]);

        $this->assertEquals(1, $items->count());
    }

    public function testRebuildValues()
    {
        $cache = $this->getCache()->setName("test_15");
        $cache->dropAll();
        $cache->getItems()->drop();
        $cache->getCriterias()->dropAll();

        $cache = $this->getCache()->setName("test_15");
        $items = $cache->getItems();
        $this->assertEquals(0, $items->count());
        $this->assertEquals(0, count($cache->getCriterias()->getAll()));
        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Jo', 'country' => 'china']);
        $items->add('m102', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Li', 'country' => 'china']);
        $items->add('m103', ['sex' => 'F', 'sex_text' => 'Female', 'name' => 'Mae', 'country' => 'hong kong']);

        $cache = $this->getCache()->setName("test_15");
        $sex = $cache->getCriterias()->add("sex");

        $this->assertEquals(0, count($sex->getValues()->getAll()));

        $cache->getJobs()->makeAll();
        $this->assertEquals(2, count($sex->getValues()->getAll()));
    }

    public function testDropCriteriaInItems()
    {
        $cache = $this->getCache()->setName("test_16");
        $cache->dropAll();
        $cache->getCriterias()->add("sex");

        $items = $cache->getItems();
        $items->add('m101', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Jo', 'country' => 'china']);
        $items->add('m102', ['sex' => 'M', 'sex_text' => 'Male', 'name' => 'Li', 'country' => 'china']);
        $items->add('m103', ['sex' => 'F', 'sex_text' => 'Female', 'name' => 'Mae', 'country' => 'hong kong']);

        foreach ($items->find() as $item) {
            $this->assertArrayHasKey("sex", $item);
        }

        $cache->getCriterias()->drop("sex");
        $cache->getJobs()->makeAll();

        foreach ($items->find() as $item) {
            $this->assertArrayNotHasKey("sex", $item);
        }

    }
}
 