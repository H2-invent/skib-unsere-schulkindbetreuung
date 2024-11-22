<?php

namespace App\Twig\Runtime;

use App\Entity\Kind;
use App\Repository\KindRepository;
use App\Repository\ZeitblockRepository;
use Twig\Extension\RuntimeExtensionInterface;

class WartelisteExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ZeitblockRepository $zeitblockRepository,
        private KindRepository $kindrepository,
    )
    {
        // Inject dependencies if needed
    }

    public function showWarteListForChild(Kind $kind)
    {
       return $this->zeitblockRepository->findWartelisteForChild($kind);
    }
    public function findLatestChildForChild(Kind $kind)
    {
        return $this->kindrepository->findOneBy(['tracing'=> $kind->getTracing()],['startDate'=>'DESC']);
    }
}
