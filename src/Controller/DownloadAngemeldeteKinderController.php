<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Repository\ActiveRepository;
use App\Repository\AutoBlockAssignmentChildRepository;
use App\Repository\KindRepository;
use App\Repository\SchuleRepository;
use App\Repository\ZeitblockRepository;
use App\Service\BerechnungsService;
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
        $activeSheet->setCellValue('E1', 'Gebuchte Zeiten');
        $activeSheet->setCellValue('E2', 'Montag');
        $activeSheet->setCellValue('F2', 'Dienstag');
        $activeSheet->setCellValue('G2', 'Mittwoch');
        $activeSheet->setCellValue('H2', 'Donnerstag');
        $activeSheet->setCellValue('I2', 'Freitag');
        $activeSheet->setCellValue('J1', 'Angemeldete Zeiten');
        $activeSheet->setCellValue('J2', 'Montag');
        $activeSheet->setCellValue('K2', 'Dienstag');
        $activeSheet->setCellValue('L2', 'Mittwoch');
        $activeSheet->setCellValue('M2', 'Donnerstag');
        $activeSheet->setCellValue('N2', 'Freitag');
        $activeSheet->setCellValue('O1', 'Potentielle Zeiten (Auto Zuteilung)');
        $activeSheet->setCellValue('O2', 'Montag');
        $activeSheet->setCellValue('P2', 'Dienstag');
        $activeSheet->setCellValue('Q2', 'Mittwoch');
        $activeSheet->setCellValue('R2', 'Donnerstag');
        $activeSheet->setCellValue('S2', 'Freitag');
        $activeSheet->setCellValue('T1', 'Gebühr (€)');
        $activeSheet->getStyle('2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        foreach (range('A', 'T') as $column) {
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
            $activeSheet->setCellValue('B' . $counter, $kind->getEltern()->getVorname());
            $activeSheet->setCellValue('C' . $counter, $kind->getVorname());
            $activeSheet->setCellValue('D' . $counter, $kind->getNachname());

            foreach ($kind->getZeitblocks() as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('E', 'I'));
            }
            foreach ($kind->getBeworben() as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('J', 'N'));
            }
            foreach ($this->getAcceptedAutoAssignedZeitblocks($kind) as $block) {
                $this->writeTimesForBlock($block, $activeSheet, $counter, range('O', 'S'));
            }

            $activeSheet->setCellValue('T' . $counter,
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

        $activeSheet->setCellValue($column . $counter,
            ($activeSheet->getCell($column . $counter)->getValue()
                ? $activeSheet->getCell($column . $counter)->getValue() . "\n"
                : ''
            )
            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
        );
        $activeSheet->getStyle($column . $counter)->getAlignment()->setWrapText(true);
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

