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
         */
        $stadt = $user->getStadt();
        $schuljahre = $stadt->getActives()->toArray();
        $form = $this->createForm(KvjsType::class, null, ['schuljahre' => $schuljahre]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $type = $data['type'];
            $childs = $this->childSearchService->searchChild(['schuljahr' => $data['schuljahr']], $user->getOrganisation(), false, $user, new $data['datum']);

            $csvData = [];
            $csvData[] = [
                'Vorname', 'Nachname', 'Geburtsdatum (TT.MM.JJJJ)', 'Geschlecht (m/w/d)',
                'Nimmt nimmt B3 in Anspruch (ja/nein)', 'Anzahl Stunden B3 (darf dann nicht leer oder 0 sein, wenn ja in Spalte davor bei Plausibilisierung',
                'Nimmt nimmt B4 in Anspruch (ja/nein)', 'Anzahl Stunden B4'
            ];

            foreach ($childs as $child) {
                $time = 0;
                foreach ($child->getZeitblocks() as $zeitblock) {
                    $diff = $zeitblock->getBis()->diff($zeitblock->getVon());
                    $time += ($diff->h * 60) + $diff->i;
                }

                $row = [
                    $child->getVorname(),
                    $child->getNachname(),
                    $child->getGeburtstag()->format('d.m.Y'),
                    'n/a',
                    $type === 'b3' ? 'ja' : 'nein',
                    $type === 'b3' ? number_format($time / 60, 2, ',', '') : '',
                    $type === 'b4' ? 'ja' : 'nein',
                    $type === 'b4' ? number_format($time / 60, 2, ',', '') : ''
                ];

                $csvData[] = $row;
            }

            $fileName = 'KVJS_Datei_Gafög_' . $data['datum']->format('Ymd') . '.csv';

            $response = new Response();
            $response->setContent($this->arrayToCsv($csvData));
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . $fileName . '"');

            return $response;
        }

        return $this->render('kvjs/index.html.twig', [
            'controller_name' => 'KvjsController',
            'form' => $form->createView(),
        ]);
    }

    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        return stream_get_contents($output);
    }
}
