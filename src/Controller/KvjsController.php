<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Entity\Stadt;
use App\Form\KvjsType;
use App\Service\ChildSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kvjs', name: 'app_kvjs_')]
class KvjsController extends AbstractController
{
    public function __construct(
        private ChildSearchService $childSearchService
    )
    {
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
        $availableSchools = $user->getSchulen()->count() === 0
            ? $user->getOrganisation()->getSchule()->toArray()
            : $user->getSchulen()->toArray();

        $form = $this->createForm(KvjsType::class, null, [
            'schuljahre' => $schuljahre,
            'schulen' => $availableSchools,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selectedSchool = $data['schule'];

            if ($selectedSchool === '__all__') {
                return $this->createZipResponseForAllSchools($availableSchools, $data, $user);
            }

            $school = $this->findSchoolFromAvailableSchools($availableSchools, (int)$selectedSchool);
            if (!$school instanceof Schule) {
                throw $this->createNotFoundException('Die ausgewählte Schule ist nicht verfügbar.');
            }

            $csvContent = $this->buildCsvForSchool($school, $data, $user);
            $fileName = sprintf(
                'KVJS_Datei_Gafoeg_%s_%s.csv',
                $this->normalizeForFileName($school->getName()),
                $data['datum']->format('Ymd')
            );

            $response = new Response();
            $response->setContent($csvContent);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . $fileName . '"');

            return $response;
        }

        return $this->render('kvjs/index.html.twig', [
            'controller_name' => 'KvjsController',
            'form' => $form->createView(),
        ]);
    }

    private function createZipResponseForAllSchools(array $schools, array $data, $user): BinaryFileResponse
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'kvjs_zip_');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($schools as $school) {
            $csvContent = $this->buildCsvForSchool($school, $data, $user);
            $fileName = sprintf(
                'KVJS_Datei_Gafoeg_%s_%s.csv',
                $this->normalizeForFileName($school->getName()),
                $data['datum']->format('Ymd')
            );
            $zip->addFromString($fileName, $csvContent);
        }

        $zip->close();

        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'KVJS_Dateien_' . $data['datum']->format('Ymd') . '.zip'
        );
        $response->headers->set('Content-Type', 'application/zip');
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private function buildCsvForSchool(Schule $school, array $data, $user): string
    {
        $childs = $this->childSearchService->searchChild(
            [
                'schuljahr' => $data['schuljahr'],
                'schule' => $school->getId(),
            ],
            $user->getOrganisation(),
            false,
            $user,
            $data['datum']
        );

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

            $csvData[] = [
                $child->getVorname(),
                $child->getNachname(),
                $child->getGeburtstag()->format('d.m.Y'),
                'n/a',
                $data['type'] === 'b3' ? 'ja' : 'nein',
                $data['type'] === 'b3' ? number_format($time / 60, 2, ',', '') : '',
                $data['type'] === 'b4' ? 'ja' : 'nein',
                $data['type'] === 'b4' ? number_format($time / 60, 2, ',', '') : ''
            ];
        }

        return $this->arrayToCsv($csvData);
    }

    private function findSchoolFromAvailableSchools(array $availableSchools, int $selectedSchoolId): ?Schule
    {
        foreach ($availableSchools as $school) {
            if ($school->getId() === $selectedSchoolId) {
                return $school;
            }
        }

        return null;
    }

    private function normalizeForFileName(?string $value): string
    {
        if (!$value) {
            return 'schule';
        }

        $normalized = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($value));
        return trim($normalized ?: 'schule', '_');
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
