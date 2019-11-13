<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\Query\Expr\Math;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BerichtController extends AbstractController
{
    /**
     * @Route("/city_report/index", name="stadt_bericht_index")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks', 'b');

        //$qb = $repo->createQueryBuilder('b');
        $blocks = array();

        foreach ($stadt->getSchules() as $key => $data) {
            $qb->orWhere('b.schule = :schule' . $key)
                ->setParameter('schule' . $key, $data);
        }
        $jahr = null;
        if ($request->get('schuljahr')) {
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $qb->andWhere('b.active = :jahr')
                ->setParameter('jahr', $jahr);
        }

        $qb->andWhere('k.fin = 1');
        $query = $qb->getQuery();
        $kinder = $result = $query->getResult();
        $schuljahre = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt));
        return $this->render('bericht/index.html.twig', array('kinder' => $kinder, 'schuljahre' => $schuljahre, 'active'=>$jahr,'stadt' => $stadt));
    }

    /**
     * @Route("/city_report/export", name="stadt_bericht_export")
     */
    public function export(Request $request, TranslatorInterface $translator)
    {
        $blocks = array();
        $kinder = array();
        $eltern = array();
        $elternT = array();
        $kinderT = array();
        $spreadsheeet = new Spreadsheet();

        $writer = new Xlsx($spreadsheeet);
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        if ($request->get('schuljahr')) {
            $schuljahr = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt, 'id' => $request->get('schuljahr')));

        } else {
            $schuljahr = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt));

        }

        foreach ($schuljahr as $data) {
            $blocks = array_merge($blocks, $data->getBlocks()->toArray());
        }

        foreach ($blocks as $data) {

            $kinderT = array_merge($kinderT, $data->getKindwithFin());
        }

        foreach ($kinderT as $data) {
            $kinder[$data->getId()] = $data;
        }

        foreach ($kinder as $data) {
            $elternT[] = $data->getEltern();
        }
        foreach ($elternT as $data) {
            $eltern[$data->getId()] = $data;
        }


        $alphas = range('a', 'z');

        // hier wird das block sheet erstellt
        $blocksheet = $spreadsheeet->createSheet();
        $blocksheet->setTitle($translator->trans('Betreuungsblöcke'));
        $blocksheet->setCellValue('A1', 'ID');
        $blocksheet->setCellValue('B1', $translator->trans('Von'));
        $blocksheet->setCellValue('C1', $translator->trans('Bis'));
        $blocksheet->setCellValue('D1', $translator->trans('Wochentag'));
        $blocksheet->setCellValue('E1', $translator->trans('Wochentag Numerisch'));
        $blocksheet->setCellValue('F1', $translator->trans('Typ'));
        $blocksheet->setCellValue('G1', $translator->trans('Typ Numerisch'));
        $blocksheet->setCellValue('H1', $translator->trans('Preise'));
        $blocksheet->setCellValue('I1', $translator->trans('Schuljahr von'));
        $blocksheet->setCellValue('J1', $translator->trans('Schuljahr Bis'));
        $blocksheet->setCellValue('K1', $translator->trans('Anzahl Kinder'));
        $counter = 2;
        foreach ($blocks as $data) {
            $count = 0;
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getId());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getVon()->format('H:i'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getBis()->format('H:i'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getWochentag());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $translator->trans($data->getWochentagString()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $translator->trans($data->getGanztagString()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getGanztag());
            $blocksheet->setCellValue($alphas[$count++] . $counter, json_encode($data->getPreise()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getActive()->getVon()->format('d.m.Y'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getActive()->getBis()->format('d.m.Y'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getKindwithFin()));

            $counter++;
        }
        $kindSheet = $spreadsheeet->createSheet();
        $kindSheet->setTitle($translator->trans('Kinder'));
        $kindSheet->setCellValue('A1', 'ID');
        $kindSheet->setCellValue('B1', $translator->trans('Alter'));
        $kindSheet->setCellValue('C1', $translator->trans('Klasse'));
        $kindSheet->setCellValue('D1', $translator->trans('Typ'));
        $kindSheet->setCellValue('E1', $translator->trans('Typ Numerisch'));
        $kindSheet->setCellValue('F1', $translator->trans('Schule'));
        $kindSheet->setCellValue('G1', $translator->trans('Eltern'));
        $counter = 2;
        foreach ($kinder as $data) {
            $count = 0;

            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getId());
            $kindSheet->setCellValue($alphas[$count++] . $counter, ($data->getGeburtstag()->diff($data->getEltern()->getCreatedAt()))->y);
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getKlasse());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArtString());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getId());
            $counter++;
        }
        $elternSheet = $spreadsheeet->createSheet();
        $elternSheet->setTitle($translator->trans('Eltern'));
        $count = 0;
        $elternSheet->setCellValue($alphas[$count++] . '1', 'ID');
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Kinder im KiGa'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Berufliche Situation'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Berufliche Situation numerisch'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Alleinerziehend'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Einkommensgruppe'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $translator->trans('Anzahl an Kindern'));
        $counter = 2;
        foreach ($eltern as $data) {
            $count = 0;
            //$data = new Stammdaten();

            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getId());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getKinderImKiga());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getBeruflicheSituationString());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getBeruflicheSituation());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getAlleinerziehend());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getEinkommen());
            $elternSheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getKinds()));
            $counter++;
        }
        $sheetIndex = $spreadsheeet->getIndex(
            $spreadsheeet->getSheetByName('Worksheet')
        );
        $spreadsheeet->removeSheetByIndex($sheetIndex);
        // Create a Temporary file in the system
        $fileName = 'Bericht.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
       //  return 0;
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }
}
