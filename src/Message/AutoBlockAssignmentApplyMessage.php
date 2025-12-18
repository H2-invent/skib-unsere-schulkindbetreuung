<?php

namespace App\Message;

final class AutoBlockAssignmentApplyMessage
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
