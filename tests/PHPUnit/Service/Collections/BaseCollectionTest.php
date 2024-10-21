<?php

namespace App\Tests\Service\Collections;

use App\Service\Collections\BaseCollection;
use PHPUnit\Framework\TestCase;

class TestCollection extends BaseCollection
{
    public function addItem(mixed $item): void
    {
        $this->items[] = $item;
    }
}

class BaseCollectionTest extends TestCase
{
    private TestCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new TestCollection();
    }

    public function testAddItem(): void
    {
        $this->collection->addItem('item1');
        $this->collection->addItem('item2');

        $this->assertCount(2, $this->collection);
    }

    public function testAddItems(): void
    {
        $this->collection->addItems(['item3', 'item4']);

        $this->assertCount(2, $this->collection);

        $this->collection->addItems(['item5']);
        $this->assertCount(3, $this->collection);
    }

    public function testMergeCollections(): void
    {
        $otherCollection = new TestCollection();
        $otherCollection->addItems(['item6', 'item7']);

        $this->collection->mergeCollections($otherCollection);

        $this->assertCount(2, $this->collection);
    }

    public function testClear(): void
    {
        $this->collection->addItems(['item8', 'item9']);
        $this->assertCount(2, $this->collection);

        $this->collection->clear();
        $this->assertCount(0, $this->collection);
    }
}
