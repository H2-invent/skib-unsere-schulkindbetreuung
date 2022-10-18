<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Service\ExcelExport\CreateExcelDayService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TagesblockMerged extends AbstractExtension
{
    private $em;
    private $createExelDayService;
    public function __construct(EntityManagerInterface $entityManager, CreateExcelDayService $createExcelDayService)
    {
        $this->em = $entityManager;
        $this->createExelDayService = $createExcelDayService;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getStringForBlocks', array($this, 'getStringForBlocks'))
        );
    }

    public function getStringForBlocks(Kind  $kind, $wochentag)
    {
    return $this->createExelDayService->getMergedTime($kind,$wochentag);

    }

}
