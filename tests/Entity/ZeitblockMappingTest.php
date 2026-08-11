<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Zeitblock;
use Doctrine\ORM\Mapping\OneToOne;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ZeitblockMappingTest extends TestCase
{
    public function testAutoBlockAssignmentIsPersistedWithItsZeitblock(): void
    {
        $property = new ReflectionProperty(Zeitblock::class, 'autoBlockAssignmentChildZeitblock');
        $attributes = $property->getAttributes(OneToOne::class);

        self::assertCount(1, $attributes);

        /** @var OneToOne $mapping */
        $mapping = $attributes[0]->newInstance();

        self::assertContains('persist', $mapping->cascade);
    }
}
