<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class ChildExcelService
{
    private $spreadsheet;
    private $writer;
    private $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->translator = $translator;
    }

    public function generateExcel($kinder)
    {
        $alphas = range('a', 'z');
        $count = 0;
        $kindSheet = $this->spreadsheet->createSheet();
        $kindSheet->setTitle($this->translator->trans('Kinder'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Vorname'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Nachname'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Alter'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Klasse'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Typ Numerisch'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Typ'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Schule'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Eltern'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Adresse'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Notfallnummer'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Notfallkontakt'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Abholung durch'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Medikamente'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Allergien'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Bemerkung'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Glutenintollerant'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kein Schweinefleisch'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Laktoseintollerant'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf mit Sonnencreme eingecremt werden'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf an Ausflügen teilnehmen'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf alleine nach Hause'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Fotos dürfen veröffentlicht werden'));
        $counter = 2;
        foreach ($kinder as $data) {
            $count = 0;
           // $data =new Kind();
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getVorname());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getNachname());
            $kindSheet->setCellValue($alphas[$count++] . $counter, ($data->getGeburtstag()->diff($data->getEltern()->getCreatedAt()))->y);
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getKlasse());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArtString());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getVorname().' '.$data->getEltern()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getStrasse().' '.$data->getEltern()->getAdresszusatz(). ' ,'.$data->getEltern()->getPlz().' '.$data->getEltern()->getStadt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getNotfallkontakt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getNotfallName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getAbholberechtigter());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getMedikamente());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getAllergie());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getBemerkung());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getGluten());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSchweinefleisch());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getLaktose());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSonnencreme());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getAusfluege());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getAlleineHause());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getFotos());
            $counter++;
        }
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('Worksheet')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
        // Create a Temporary file in the system

        $fileName = 'Kinder.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $this->writer->save($temp_file);
        //  return 0;
        // Return the excel file as an attachment
        return $temp_file;
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }



}
