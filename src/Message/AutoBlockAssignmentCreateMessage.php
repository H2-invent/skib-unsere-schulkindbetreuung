<?php

namespace App\Message;

final class AutoBlockAssignmentCreateMessage
{
    public function __construct(
        private int $idOrganisation,
        private int $idSchuljahr,
    ) {
    }

    public function getIdOrganisation(): int
    {
        return $this->idOrganisation;
    }

    public function getIdSchuljahr(): int
    {
        return $this->idSchuljahr;
    }
}
