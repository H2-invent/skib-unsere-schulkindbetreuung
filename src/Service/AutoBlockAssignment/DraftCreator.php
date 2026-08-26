<?php
declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\Active;
use App\Entity\AutoBlockAssignment;
use App\Entity\AutoBlockAssignmentChild;
use App\Entity\AutoBlockAssignmentChildZeitblock;
use App\Entity\Organisation;
use App\Repository\AutoBlockAssignmentChildRepository;
use App\Repository\AutoBlockAssignmentRepository;
use App\Repository\KindRepository;
use App\Repository\ZeitblockRepository;
use App\Service\WeightScoreService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;

class DraftCreator
{
    use LoggerAwareTrait;

    public function __construct(
        private AutoBlockAssignmentRepository $autoBlockAssignmentRepository,
        private AutoBlockAssignmentChildRepository $autoBlockAssignmentChildRepository,
        private ZeitblockRepository $zeitblockRepository,
        private KindRepository $kindRepository,
        private EntityManagerInterface $entityManager,
        private DraftCreationValidator $draftCreationValidator,
        private WeightScoreService $weightScoreService,
    )
    {
    }

    public function create(Organisation $organisation, Active $schuljahr): void
    {
        $autoBlockAssignment = (new AutoBlockAssignment())
            ->setOrganisation($organisation)
        ;

        $this->calculateWeights($organisation, $autoBlockAssignment, $schuljahr);
        $this->assignZeitblocks($organisation, $autoBlockAssignment, $schuljahr);
    }

    private function calculateWeights(Organisation $organisation, AutoBlockAssignment $autoBlockAssignment, Active $schuljahr): void
    {
        $kinder = $this->kindRepository->findKindWithBeworbenZeitblocksForSchuljahr($organisation, $schuljahr);
        $weightScoreCalculator = $this->weightScoreService->createCalculator($organisation);

        foreach ($kinder as $kind) {
            $weight = $weightScoreCalculator($kind);

            $autoBlockAssignmentChild = (new AutoBlockAssignmentChild())
                ->setAutoBlockAssignment($autoBlockAssignment)
                ->setKind($kind)
                ->setWeight($weight)
            ;
            $autoBlockAssignment->addChild($autoBlockAssignmentChild);
            $this->entityManager->persist($autoBlockAssignmentChild);
        }

        $this->entityManager->persist($autoBlockAssignment);
        $this->entityManager->flush();
    }

    private function assignZeitblocks(Organisation $organisation, AutoBlockAssignment $autoBlockAssignment, Active $schuljahr): void
    {
        $children = $this->autoBlockAssignmentChildRepository->findByAutoBlockAssignmentWeighted($autoBlockAssignment);
        $minBlocksPerDay = $organisation->getStadt()?->getMinBlocksPerDay();
        $minDaysPerWeek = $organisation->getStadt()->getMinDaysperWeek();

        foreach ($children as $child) {
            $kind = $child->getKind();
            if ($kind === null) {
                continue;
            }

            $beworbenZeitblocks = $this->zeitblockRepository->findBeworbenBlocksByKindAndSchuljahr($kind, $schuljahr);
            $bookedZeitblocks = $this->zeitblockRepository->findBookedBlocksByKindAndSchuljahr($kind, $schuljahr);

            [$accepted, $warteschlange] = $this->draftCreationValidator->validateZeitblocks($beworbenZeitblocks, $bookedZeitblocks, $minBlocksPerDay, $minDaysPerWeek);

            foreach ($accepted as $acceptedZeitblock) {
                $autoBlockAssignmentZeitblock = (new AutoBlockAssignmentChildZeitblock())
                    ->setChild($child)
                    ->setZeitblock($acceptedZeitblock)
                    ->setAccepted(true)
                    ->setWarteschlange(false)
                ;
                $this->entityManager->persist($autoBlockAssignmentZeitblock);
            }

            foreach ($warteschlange as $warteschlangeZeitblock) {
                $autoBlockAssignmentZeitblock = (new AutoBlockAssignmentChildZeitblock())
                    ->setChild($child)
                    ->setZeitblock($warteschlangeZeitblock)
                    ->setAccepted(false)
                    ->setWarteschlange(true)
                ;
                $this->entityManager->persist($autoBlockAssignmentZeitblock);
            }
        }
    }
}
