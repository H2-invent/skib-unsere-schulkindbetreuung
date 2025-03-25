<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Form\KvjsType;
use App\Service\ChildSearchService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kvjs', name: 'app_kvjs_')]
class KvjsController extends AbstractController
{
    private $spreadSheet;

    public function __construct(
        private ChildSearchService $childSearchService
    )
    {
        $this->spreadSheet = new Spreadsheet();

    }

    #[Route('/index', name: 'index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        /**
         * @var Stadt
         * */
        $stadt = $user->getStadt();
        $schuljahre = $stadt->getActives()->toArray();
        $form = $this->createForm(KvjsType::class, null, ['schuljahre' => $schuljahre]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hier kannst du die verarbeiteten Daten nutzen
            $data = $form->getData();

            $type = $data['type'];
            $childs = $this->childSearchService->searchChild(['schuljahr'=>$data['schuljahr']],$user->getOrganisation(),false,$user,new $data['datum']);

            $kvysSheet = $this->spreadSheet->createSheet();
            $kvysSheet->setTitle('KVJS Datei Gafög')
                ->setCellValue('A1', 'Vorname')
                ->setCellValue('B1', 'Nachname')
                ->setCellValue('C1', 'Geburtsdatum (TT.MM.JJJJ)')
                ->setCellValue('D1', 'Geschlecht (m/w/d)')
                ->setCellValue('E1','Nimmt B3 in Anspruch (ja/nein)')
                ->setCellValue('F1','Anzahl Stunden B3 (darf dann nicht leer oder 0 sein, wenn ja in Spalte davor bei Plausibilisierung')
                ->setCellValue('G1','Nimmt B4 in Anspruch (ja/nein)')
                ->setCellValue('H1','Anzahl Stunden B4');
            $count = 2;
            foreach ($childs as $child) {
                $kvysSheet->setCellValue('A'.$count, $child->getVorname())
                    ->setCellValue('B'.$count, $child->getNachname())
                    ->setCellValue('C'.$count,$child->getGeburtstag()->format('d.m.Y'))
                    ->setCellValue('D'.$count,'n/a');
                $time = 0;
                foreach ($child->getZeitblocks() as $zeitblock) {
                    $diff = $zeitblock->getBis()->diff($zeitblock->getVon());
                    $time +=($diff->h*60)+$diff->i;
                }
                if ($type === 'b3'){
                    $kvysSheet->setCellValue('E'.$count,'ja')
                    ->setCellValue('F'.$count,$time/60)
                        ->setCellValue('G'.$count,'Nein')
                        ->setCellValue('H'.$count,0);
                }else if ($type === 'b4'){
                    $kvysSheet->setCellValue('E'.$count,'Nein')
                        ->setCellValue('F'.$count,'0')
                        ->setCellValue('G'.$count,'ja')
                        ->setCellValue('H'.$count,$time/60);
                }
                $count++;
            }
            $sheetIndex = $this->spreadSheet->getIndex(
                $this->spreadSheet->getSheetByName('Worksheet')
            );
            $this->spreadSheet->removeSheetByIndex($sheetIndex);
            $this->spreadSheet->setActiveSheetIndex(0);
            $writer = new Xlsx($this->spreadSheet);


            // Create a Temporary file in the system
            $fileName = 'KVJS Datei Gafög'.$data['datum']->format('Ymd').'.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

            // Create the excel file in the tmp directory of the system
            $writer->save($temp_file);

            // Return the excel file as an attachment
            return $this->file($temp_file,  $fileName , ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        }

        return $this->render('kvjs/index.html.twig', [
            'controller_name' => 'KvjsController',
            'form' => $form->createView(),
        ]);
    }
}
