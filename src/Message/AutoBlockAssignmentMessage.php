<?php

namespace App\Message;

final class AutoBlockAssignmentMessage
{
    public function __construct(
        private int $idOrganisation
    )
    {
    }

    public function getIdOrganisation(): int
    {
        return $this->idOrganisation;
    }
}
