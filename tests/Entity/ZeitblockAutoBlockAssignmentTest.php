<?php

namespace App\Tests\Entity;

use App\Entity\AutoBlockAssignmentChildZeitblock;
use App\Entity\Zeitblock;
use PHPUnit\Framework\TestCase;

class ZeitblockAutoBlockAssignmentTest extends TestCase
{
    public function testAutoBlockAssignmentReferenceCanBeClearedWithoutDeletingZeitblock(): void
    {
        $zeitblock = new Zeitblock();
        $assignmentZeitblock = new AutoBlockAssignmentChildZeitblock();

        $zeitblock->setAutoBlockAssignmentChildZeitblock($assignmentZeitblock);

        self::assertSame($assignmentZeitblock, $zeitblock->getAutoBlockAssignmentChildZeitblock());
        self::assertSame($zeitblock, $assignmentZeitblock->getZeitblock());

        $zeitblock->setAutoBlockAssignmentChildZeitblock(null);

        self::assertNull($zeitblock->getAutoBlockAssignmentChildZeitblock());
        self::assertSame($zeitblock, $assignmentZeitblock->getZeitblock());
    }
}
