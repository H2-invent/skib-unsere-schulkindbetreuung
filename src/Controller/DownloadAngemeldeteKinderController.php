<?php

namespace App\Controller;

use App\Repository\ActiveRepository;
use App\Repository\SchuleRepository;
use App\Repository\ZeitblockRepository;
use App\Service\BerechnungsService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/org_accept/download', name: 'download_angemeldete')]
class DownloadAngemeldeteKinderController extends AbstractController
{
    private $writer;
    private $spreadsheet;
    private $activeSheet;

    public function __construct(
        private SchuleRepository    $schuleRepository,
        private ActiveRepository    $activeRepository,
        private ZeitblockRepository $zeitblockRepository,
        private BerechnungsService $berechnungsService,
    )
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->activeSheet = $this->spreadsheet->getActiveSheet();
        $this->activeSheet->setCellValue('A1', 'Eltern Vorname');
        $this->activeSheet->setCellValue('B1', 'Eltern Nachname');
        $this->activeSheet->setCellValue('D1', 'Vorname');
        $this->activeSheet->setCellValue('E1', 'Nachname');
        $this->activeSheet->setCellValue('F1', 'Gebuchte Zeiten');
        $this->activeSheet->setCellValue('F2', 'Montag');
        $this->activeSheet->setCellValue('G2', 'Dienstag');
        $this->activeSheet->setCellValue('H2', 'Mittwoch');
        $this->activeSheet->setCellValue('I2', 'Donnerstag');
        $this->activeSheet->setCellValue('J2', 'Freitag');
        $this->activeSheet->setCellValue('K1', 'Angemeldete Zeiten');
        $this->activeSheet->setCellValue('K2', 'Montag');
        $this->activeSheet->setCellValue('L2', 'Dienstag');
        $this->activeSheet->setCellValue('M2', 'Mittwoch');
        $this->activeSheet->setCellValue('N2', 'Donnerstag');
        $this->activeSheet->setCellValue('O2', 'Freitag');
        $this->activeSheet->setCellValue('P1', 'Gebühr (€)');
    }

    #[
        Route('/kinder', name: '_kinder')]
    public function kinder(Request $request)
    {
        $schule = $this->schuleRepository->find($request->get('schule_id'));
        $active = $this->activeRepository->find($request->get('active_id'));

        if (!in_array($schule, $this->getUser()->getSchulen()->toArray())) {
            throw new NotFoundHttpException('Schule not found');
        }
        if (!$active) {
            throw new NotFoundHttpException('Schule not found');
        }
        $blocks = $this->zeitblockRepository->findBy(array('active' => $active, 'schule' => $schule));
        $kinder = [];
        foreach ($blocks as $data) {
            $kinder = array_merge($kinder, $data->getKinderBeworben()->toArray());
        }
        $kinder = array_unique($kinder);
        $counter = 3;
        foreach ($kinder as $data2) {
            if ($data2->getStartDate() && $data2->getEltern()->getCreatedAt()) {
                $this->activeSheet->setCellValue('A' . $counter, $data2->getEltern()->getVorname());
                $this->activeSheet->setCellValue('B' . $counter, $data2->getEltern()->getVorname());
                $this->activeSheet->setCellValue('D' . $counter, $data2->getVorname());
                $this->activeSheet->setCellValue('E' . $counter, $data2->getNachname());
                foreach ($data2->getZeitblocks() as $block) {
                    switch ($block->getWochentag()) {
                        case 0:
                            $this->activeSheet->setCellValue('F' . $counter,
                                $this->activeSheet->getCell('F' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('F' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 1:
                            $this->activeSheet->setCellValue('G' . $counter,
                                $this->activeSheet->getCell('F' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('G' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 2:
                            $this->activeSheet->setCellValue('H' . $counter,
                                $this->activeSheet->getCell('F' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('H' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 3:
                            $this->activeSheet->setCellValue('I' . $counter,
                                $this->activeSheet->getCell('F' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('I' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 4:
                            $this->activeSheet->setCellValue('J' . $counter,
                                $this->activeSheet->getCell('F' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('J' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        default:
                            break;
                    }

                }
                foreach ($data2->getBeworben() as $block) {
;                    switch ($block->getWochentag()) {
                        case 0:
                            $this->activeSheet->setCellValue('K' . $counter,
                                $this->activeSheet->getCell('K' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('K' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 1:
                            $this->activeSheet->setCellValue('L' . $counter,
                                $this->activeSheet->getCell('L' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')

                            );
                            $this->activeSheet->getStyle('L' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 2:
                            $this->activeSheet->setCellValue('M' . $counter,
                                $this->activeSheet->getCell('M' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('M' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 3:
                            $this->activeSheet->setCellValue('N' . $counter,
                                $this->activeSheet->getCell('N' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('N' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        case 4:
                            $this->activeSheet->setCellValue('O' . $counter,
                                $this->activeSheet->getCell('O' . $counter)->getValue()
                                . "\n" . $block->getvon()->format('H:i') . '-' . $block->getbis()->format('H:i')
                            );
                            $this->activeSheet->getStyle('O' . $counter)->getAlignment()->setWrapText(true);
                            break;
                        default:
                            break;
                    }

                }

                $this->activeSheet->setCellValue('P' . $counter, $this->berechnungsService->getPreisforBetreuung($data2,true,$data2->getStartDate()));
            }
            $counter++;
        }


        $this->spreadsheet->setActiveSheetIndex(0);


        // Create a Temporary file in the system
        $fileName = 'Angemeldete Kinder_'.$schule->getName().'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $this->writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file,  $fileName . '.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);

    }
}

