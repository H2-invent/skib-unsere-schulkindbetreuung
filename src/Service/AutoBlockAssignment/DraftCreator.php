<?php
declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\AutoBlockAssignment;
use App\Entity\AutoBlockAssignmentChild;
use App\Entity\AutoBlockAssignmentChildZeitblock;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use App\Repository\AutoBlockAssignmentChildRepository;
use App\Repository\AutoBlockAssignmentRepository;
use App\Repository\ZeitblockRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class DraftCreator
{
    use LoggerAwareTrait;

    public function __construct(
        private ExpressionLanguage $expressionLanguage,
        private AutoBlockAssignmentRepository $autoBlockAssignmentRepository,
        private AutoBlockAssignmentChildRepository $autoBlockAssignmentChildRepository,
        private ZeitblockRepository $zeitblockRepository,
        private EntityManagerInterface $entityManager,
        private DraftCreationValidator $draftCreationValidator,
    )
    {
    }

    public function create(Organisation $organisation): void
    {
        $autoBlockAssignment = (new AutoBlockAssignment())
            ->setOrganisation($organisation);


        $this->calculateWeights($organisation, $autoBlockAssignment);
        $this->assignZeitblocks($organisation, $autoBlockAssignment);
    }

    private function calculateWeights(Organisation $organisation, AutoBlockAssignment $autoBlockAssignment): void
    {
        $stadt = $organisation->getStadt();
        if ($stadt === null) {
            throw new RuntimeException("No stadt found for organisation: " . $organisation->getId());
        }

        $formulaString = $stadt->getAutoAssignFormula();
        $formulaParsed = $this->expressionLanguage->parse($formulaString, ['kind', 'eltern', 'schule', 'organisation']);

        foreach ($organisation->getSchule() as $schule) {
            foreach ($schule->getKinder() as $kind) {
                $weight = $this->expressionLanguage->evaluate($formulaParsed, [
                    'kind' => $kind,
                    'eltern' => $kind->getEltern(),
                    'schule' => $schule,
                    'organisation' => $organisation,
                ]);

                $autoBlockAssignmentChild = (new AutoBlockAssignmentChild())
                    ->setAutoBlockAssignment($autoBlockAssignment)
                    ->setKind($kind)
                    ->setWeight((float)$weight);
                $autoBlockAssignment->addChild($autoBlockAssignmentChild);
                $this->entityManager->persist($autoBlockAssignmentChild);
            }
        }

        $this->entityManager->persist($autoBlockAssignment);
        $this->entityManager->flush();
    }

    private function assignZeitblocks(Organisation $organisation, AutoBlockAssignment $autoBlockAssignment): void
    {
        $children = $this->autoBlockAssignmentChildRepository->findByAutoBlockAssignmentWeighted($autoBlockAssignment);
        foreach ($children as $child) {
            $kind = $child->getKind();
            if ($kind === null) {
                continue;
            }

            $beworbenZeitblocks = $this->zeitblockRepository->findBeworbenBlocksByKind($kind);

            [$accepted, $warteschlange] = $this->draftCreationValidator->validateZeitblocks($beworbenZeitblocks);

            foreach ($accepted as $acceptedZeitblock) {
                $autoBlockAssignmentZeitblock = (new AutoBlockAssignmentChildZeitblock())
                    ->setChild($child)
                    ->setZeitblock($acceptedZeitblock)
                    ->setAccepted(true)
                    ->setWarteschlange(false);
                $this->entityManager->persist($autoBlockAssignmentZeitblock);
            }

            foreach ($warteschlange as $warteschlangeZeitblock) {
                $autoBlockAssignmentZeitblock = (new AutoBlockAssignmentChildZeitblock())
                    ->setChild($child)
                    ->setZeitblock($warteschlangeZeitblock)
                    ->setAccepted(false)
                    ->setWarteschlange(true);
                $this->entityManager->persist($autoBlockAssignmentZeitblock);
            }
        }
    }
}
