<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use App\Entity\Entity;

class EntityTraitTest extends TestCase
{
    private object $entity;

    protected function setUp(): void
    {
        // Create a mock class that uses the Entity trait
        $this->entity = new class {
            use Entity;

            public function serialize(): array
            {
                return [];
            }
        };
    }

    public function testGetIdInitiallyReturnsNull(): void
    {
        $this->assertNull($this->entity->getId());
    }

    public function testSetId(): void
    {
        // Set ID for the first time
        $this->entity->setId(10);
        $this->assertEquals(10, $this->entity->getId());

        // Try setting ID again, it should remain the same
        $this->entity->setId(20);
        $this->assertEquals(10, $this->entity->getId()); // ID shouldn't change once set
    }

    public function testSetCreatedAtValue(): void
    {
        // Call PrePersist to set the createdAt and updatedAt values
        $this->entity->setCreatedAtValue();
        $createdAt = $this->entity->getCreatedAt();
        $updatedAt = $this->entity->getUpdatedAt();

        $this->assertInstanceOf(DateTimeImmutable::class, $createdAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $updatedAt);
        $this->assertEquals($createdAt->format('Y-m-d H:i:s'), $updatedAt->format('Y-m-d H:i:s'));
    }

    public function testSetUpdatedAtValue(): void
    {
        // Call PrePersist to set the initial createdAt and updatedAt
        $this->entity->setCreatedAtValue();
        $initialUpdatedAt = $this->entity->getUpdatedAt();

        // Simulate some time has passed
        sleep(1);

        // Call PreUpdate to set the updatedAt
        $this->entity->setUpdatedAtValue();
        $updatedAt = $this->entity->getUpdatedAt();

        $this->assertInstanceOf(DateTimeImmutable::class, $updatedAt);
        $this->assertGreaterThan($initialUpdatedAt, $updatedAt); // updatedAt should be later than initial
    }
}
