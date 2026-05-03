<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Repository\ActiveRepository;
use App\Repository\KindRepository;
use App\Repository\SchuleRepository;
use App\Repository\ZeitblockRepository;
use App\Service\BerechnungsService;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/org_accept/download', name: 'download_angemeldete')]
class DownloadAngemeldeteKinderController extends AbstractController
{
    public function __construct(
        private SchuleRepository $schuleRepository,
        private ActiveRepository $activeRepository,
        private ZeitblockRepository $zeitblockRepository,
        private BerechnungsService $berechnungsService,
        private KindRepository $kindRepository,
    )
    {
    }

    /**
     * @return list<Spreadsheet, Worksheet, Xlsx>
     */
    private function initSheet(): array
    {
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setCellValue('A1', 'Eltern Vorname');
        $activeSheet->setCellValue('B1', 'Eltern Nachname');
        $activeSheet->setCellValue('C1', 'Vorname');
        $activeSheet->setCellValue('D1', 'Nachname');
        $activeSheet->setCellValue('E1', 'E-Mail');
        $activeSheet->setCellValue('F1', 'Gebuchte Zeiten');
        $activeSheet->setCellValue('F2', 'Montag');
        $activeSheet->setCellValue('G2', 'Dienstag');
        $activeSheet->setCellValue('H2', 'Mittwoch');
        $activeSheet->setCellValue('I2', 'Donnerstag');
        $activeSheet->setCellValue('J2', 'Freitag');
        $activeSheet->setCellValue('K1', 'Angemeldete Zeiten');
        $activeSheet->setCellValue('K2', 'Montag');
        $activeSheet->setCellValue('L2', 'Dienstag');
        $activeSheet->setCellValue('M2', 'Mittwoch');
        $activeSheet->setCellValue('N2', 'Donnerstag');
        $activeSheet->setCellValue('O2', 'Freitag');
        $activeSheet->setCellValue('P1', 'Potentielle Zeiten (Auto Zuteilung)');
        $activeSheet->setCellValue('P2', 'Montag');
        $activeSheet->setCellValue('Q2', 'Dienstag');
        $activeSheet->setCellValue('R2', 'Mittwoch');
        $activeSheet->setCellValue('S2', 'Donnerstag');
        $activeSheet->setCellValue('T2', 'Freitag');
        $activeSheet->setCellValue('U1', 'Gewichtungsscore');
        $activeSheet->setCellValue('V1', 'Klasse');
        $activeSheet->setCellValue('W1', 'Berufsstatus');
        $activeSheet->setCellValue('X1', 'Alleinerziehend');
        $activeSheet->setCellValue('Y1', 'Anzahl Dokumente');
        $activeSheet->setCellValue('Z1', 'Gebühr (€)');
        $activeSheet->getStyle('2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        foreach (range('A', 'Z') as $column) {
            $activeSheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [$spreadsheet, $activeSheet, $writer];
    }

    #[Route('/kinder', name: '_kinder')]
    public function kinder(Request $request): BinaryFileResponse
    {
        [$spreadsheet, $activeSheet, $writer] = $this->initSheet();

        $schule = $this->schuleRepository->find($request->get('schule_id'));
        $active = $this->activeRepository->find($request->get('active_id'));

        $orgSchulen = $this->getUser()?->getOrganisation()?->getSchule()?->toArray();
        $userSchulen = $this->getUser()?->getSchulen()?->toArray();

        if ($schule === null || !in_array($schule, [...$orgSchulen, ...$userSchulen], true)) {
            throw new NotFoundHttpException('Schule not found');
        }
        if (!$active) {
            throw new NotFoundHttpException('Schule not found');
        }

        $status = $request->get('status');
        $autoBlocks = $this->zeitblockRepository->findBy(['active' => $active, 'schule' => $schule]);
        $kinder = [];
        foreach ($autoBlocks as $block) {
            if ($status === 'alle') {
                $kinder = array_merge(
                    $kinder,
                    $block->getKinderBeworben()->toArray(),
                    $block->getKind()->toArray(),
                    $this->kindRepository->findAutoBlockAssignedKindByZeitblock($block)
                );
            } else {
                $kinder = array_merge($kinder, $block->getKinderBeworben()->toArray());
            }
        }
        $kinder = $this->deduplicateKinderChooseNewest($kinder);
        $counter = 3;
        foreach ($kinder as $kind) {
            if ($kind->getStartDate() === null || $kind->getEltern()->getCreatedAt() === null) {
                continue;
            }

            $activeSheet->setCellValue('A' . $counter, $kind->getEltern()->getVorname());
            $activeSheet->setCellValue('B' . $counter, $kind->getEltern()->getName());
            $activeSheet->setCellValue('C' . $counter, $kind->getVorname());
            $activeSheet->setCellValue('D' . $counter, $kind->getNachname());
            $activeSheet->setCellValue('E' . $counter, $kind->getEltern()->getEmail());

            foreach ($kind->getZeitblocks() as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('F', 'J'));
            }
            foreach ($kind->getBeworben() as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('K', 'O'));
            }
            foreach ($this->getAcceptedAutoAssignedZeitblocks($kind) as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('P', 'T'));
            }

            $activeSheet->setCellValue('U' . $counter, $kind->getAutoBlockAssignmentChild()?->getWeight());
            $activeSheet->setCellValue('V' . $counter, $kind->getKlasseString());
            $activeSheet->setCellValue('W' . $counter, $kind->getEltern()?->getBeruflicheSituationString());
            $activeSheet->setCellValue('X' . $counter, $kind->getEltern()?->getAlleinerziehend());
            $activeSheet->setCellValue('Y' . $counter, count($kind->getEltern()?->getFile() ?? new ArrayCollection()));
            $activeSheet->setCellValue('Z' . $counter,
                $this->berechnungsService->getPreisforBetreuung($kind, true, $kind->getStartDate())
            );
            $counter++;
        }


        $spreadsheet->setActiveSheetIndex(0);


        // Create a Temporary file in the system
        $fileName = 'Angemeldete Kinder_' . $schule->getName() . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    /**
     * @param Kind[] $kinder
     * @return Kind[]
     */
    private function deduplicateKinderChooseNewest(array $kinder): array
    {
        $unique = [];
        foreach ($kinder as $kind) {
            $tracing = $kind->getTracing();

            if (!isset($unique[$tracing])) {
                $unique[$tracing] = $kind;
                continue;
            }
            if ($kind->getEltern()->getCreatedAt() > $unique[$tracing]->getEltern()->getCreatedAt()) {
                $unique[$tracing] = $kind;
            }
        }

        return $unique;
    }

    /**
     * @param array{0: string, 1: string, 2: string, 3: string, 4: string} $columnRange
     */
    private function writeTimesForBlock(Zeitblock $block, Worksheet|Xlsx $activeSheet, int $counter, array $columnRange): void
    {
        $column = $columnRange[$block->getWochentag()];
        $cell = $column . $counter;

        $activeSheet->setCellValue($cell,
            ($activeSheet->getCell($cell)->getValue()
                ? $activeSheet->getCell($cell)->getValue() . "\n"
                : ''
            )
            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
        );
        $activeSheet->getStyle($cell)->getAlignment()->setWrapText(true);
    }

    /**
     * @param Kind $kind
     * @return Zeitblock[]
     */
    private function getAcceptedAutoAssignedZeitblocks(Kind $kind): array
    {
        $autoBlocks = $kind->getAutoBlockAssignmentChild()?->getZeitblocks()->toArray() ?? [];
        $autoBlocks = array_filter($autoBlocks, static fn($autoBlock) => $autoBlock->isAccepted());

        return array_map(static fn($autoBlock) => $autoBlock->getZeitblock(), $autoBlocks);
    }
}

