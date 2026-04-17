<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Entity\Kind;
use App\Service\ExcelExport\CreateExcelDayService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TagesblockMerged extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $em,
        private CreateExcelDayService $createExelDayService,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getStringForBlocks', $this->getStringForBlocks(...)),
        ];
    }

    public function getStringForBlocks(Kind $kind, $wochentag, string $status)
    {
        return $this->createExelDayService->getMergedTime($kind, $wochentag, $status);
    }
}
