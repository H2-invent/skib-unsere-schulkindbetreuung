<?php

namespace App\Controller;

use App\Repository\ActiveRepository;
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
        $activeSheet->setCellValue('O1', 'Gebühr (€)');
        $activeSheet->getStyle('2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'] as $column) {
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
        $blocks = $this->zeitblockRepository->findBy(['active' => $active, 'schule' => $schule]);
        $kinder = [];
        foreach ($blocks as $block) {
            if ($status === 'alle') {
                $kinder = array_merge($kinder, $block->getKinderBeworben()->toArray(), $block->getKind()->toArray());
            } else {
                $kinder = array_merge($kinder, $block->getKinderBeworben()->toArray());
            }
        }
        $kinder = array_unique($kinder);
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
                switch ($block->getWochentag()) {
                    case 0:
                        $activeSheet->setCellValue('E' . $counter,
                            ($activeSheet->getCell('E' . $counter)->getValue()
                                ? $activeSheet->getCell('E' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('E' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 1:
                        $activeSheet->setCellValue('F' . $counter,
                            ($activeSheet->getCell('F' . $counter)->getValue()
                                ? $activeSheet->getCell('F' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('F' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 2:
                        $activeSheet->setCellValue('G' . $counter,
                            ($activeSheet->getCell('G' . $counter)->getValue()
                                ? $activeSheet->getCell('G' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('G' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 3:
                        $activeSheet->setCellValue('H' . $counter,
                            ($activeSheet->getCell('H' . $counter)->getValue()
                                ? $activeSheet->getCell('H' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('H' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 4:
                        $activeSheet->setCellValue('I' . $counter,
                            ($activeSheet->getCell('I' . $counter)->getValue()
                                ? $activeSheet->getCell('I' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('I' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        break;
                }

            }
            foreach ($kind->getBeworben() as $block) {
                switch ($block->getWochentag()) {
                    case 0:
                        $activeSheet->setCellValue('J' . $counter,
                            ($activeSheet->getCell('J' . $counter)->getValue()
                                ? $activeSheet->getCell('J' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('J' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 1:
                        $activeSheet->setCellValue('K' . $counter,
                            ($activeSheet->getCell('K' . $counter)->getValue()
                                ? $activeSheet->getCell('K' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')

                        );
                        $activeSheet->getStyle('K' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 2:
                        $activeSheet->setCellValue('L' . $counter,
                            ($activeSheet->getCell('L' . $counter)->getValue()
                                ? $activeSheet->getCell('L' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('L' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 3:
                        $activeSheet->setCellValue('M' . $counter,
                            ($activeSheet->getCell('M' . $counter)->getValue()
                                ? $activeSheet->getCell('M' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('M' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    case 4:
                        $activeSheet->setCellValue('N' . $counter,
                            ($activeSheet->getCell('N' . $counter)->getValue()
                                ? $activeSheet->getCell('N' . $counter)->getValue() . "\n"
                                : ''
                            )
                            . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                        );
                        $activeSheet->getStyle('N' . $counter)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        break;
                }

            }

            $activeSheet->setCellValue('O' . $counter,
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
}

