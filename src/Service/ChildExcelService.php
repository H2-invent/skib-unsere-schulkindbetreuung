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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class ChildExcelService
{
    private $spreadsheet;
    private $writer;
    private $translator;
    private $tokenStorage;
    public function __construct(TranslatorInterface $translator,TokenStorageInterface $tokenStorage)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->translator = $translator;
        $this->tokenStorage= $tokenStorage;
    }

    public function generateExcel($kinder)
    {
        $alphas = $this->createColumnsArray('ZZ');
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
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Erziehungsberechtigter'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Straße'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Adresse'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('PLZ'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Ort'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Telefonnummer'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Notfallkontakt'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Notfallnummer'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Abholung durch'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Medikamente'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Allergien'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Bemerkung'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Gluten intolerant'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kein Schweinefleisch'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Laktose intolerant'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf mit Sonnencreme eingecremt werden'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf an Ausflügen teilnehmen'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Darf alleine nach Hause'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Fotos dürfen veröffentlicht werden'));
        $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Gebuchte Betreuungszeitfenster'));

        if($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_ACCOUNTING')){
           $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kundennummer'));
           $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('IBAN'));
           $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('BIC'));
           $kindSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kontoinhaber'));
       }
        $counter = 2;
        foreach ($kinder as $data) {
            $count = 0;

            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getVorname());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getNachname());
            $kindSheet->setCellValue($alphas[$count++] . $counter, ($data->getGeburtstag()->diff($data->getEltern()->getCreatedAt()))->y);
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getKlasse());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArtString());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getVorname().' '.$data->getEltern()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getStrasse());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getAdresszusatz());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getPlz());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getStadt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getPhoneNumber());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getNotfallName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getEltern()->getNotfallkontakt());
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
            $gebucht = array();
            foreach ($data->getZeitblocks() as $data2){
                $gebucht[]=$data2->getWochentagString().': '.$data2->getVon()->format('H:i').'-'.$data2->getBis()->format('H:i');
            }
            $kindSheet->setCellValue($alphas[$count++] .$counter,implode(' | ', $gebucht));

            if($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_ACCOUNTING')){
                $kindSheet->setCellValue($alphas[$count++] .  $counter, $data->getEltern()->getCustomerID());
                $kindSheet->setCellValue($alphas[$count++] .  $counter, $data->getEltern()->getIban());
                $kindSheet->setCellValue($alphas[$count++] .  $counter, $data->getEltern()->getBic());
                $kindSheet->setCellValue($alphas[$count++] .  $counter, $data->getEltern()->getKontoinhaber());
            }
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

        // Return the excel file as an attachment
        return $temp_file;

    }
    private function createColumnsArray($end_column, $first_letters = '')
    {
        $columns = array();
        $length = strlen($end_column);
        $letters = range('A', 'Z');

        // Iterate over 26 letters.
        foreach ($letters as $letter) {
            // Paste the $first_letters before the next.
            $column = $first_letters . $letter;

            // Add the column to the final array.
            $columns[] = $column;

            // If it was the end column that was added, return the columns.
            if ($column == $end_column)
                return $columns;
        }

        // Add the column children.
        foreach ($columns as $column) {
            // Don't itterate if the $end_column was already set in a previous itteration.
            // Stop iterating if you've reached the maximum character length.
            if (!in_array($end_column, $columns) && strlen($column) < $length) {
                $new_columns = $this->createColumnsArray($end_column, $column);
                // Merge the new columns which were created with the final columns array.
                $columns = array_merge($columns, $new_columns);
            }
        }

        return $columns;
    }


}
