<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Organisation;

class AutoBlockAssignmentService
{
    public function __construct(
        private MessageB
    )
    {
    }

    public function startAsync(Organisation $organisation): void
    {

    }
}
