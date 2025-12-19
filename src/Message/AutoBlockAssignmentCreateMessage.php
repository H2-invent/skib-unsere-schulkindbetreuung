<?php

namespace App\Message;

final class AutoBlockAssignmentCreateMessage
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
