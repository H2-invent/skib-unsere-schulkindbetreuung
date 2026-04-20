<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use App\Entity\Sepa;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Contracts\Translation\TranslatorInterface;

class SepaExcel
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

    public function generateExcel(Sepa $sepa)
    {
        $alphas = range('a', 'z');
        $count = 0;
        $sepaSheet = $this->spreadsheet->createSheet();
        $sepaSheet->setTitle($this->translator->trans('SEPA Übersicht'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kundennummer'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Vorname'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Nachname'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Straße'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('PLZ'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Stadt'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Telefonnummer'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Betrag in €'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl der Kinder'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('IBAN'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('BIC'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kontoinhaber'));
        $counter = 2;
        foreach ($sepa->getRechnungen() as $data) {
            $count = 0;
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getKundennummerForOrg($sepa->getOrganisation()->getId()) ? $data->getStammdaten()->getKundennummerForOrg($sepa->getOrganisation()->getId())->getKundennummer() : "");
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getVorname());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getName());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getStrasse());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getPlz());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getStadt());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getPhoneNumber());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getSumme());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getKinder()));
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getIban());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getBic());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getKontoinhaber());
            $counter++;
        }
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('Worksheet')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
        // Create a Temporary file in the system

        $fileName = 'Sepa_ID' . $sepa->getId() . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $this->writer->save($temp_file);

        // Return the excel file as an attachment
        return $temp_file;

    }

    public function generateChildMonthlyExcel(Sepa $sepa): string
    {
        $sheet = new Spreadsheet();
        $excelWriter = new Xlsx($sheet);
        $monthlySheet = $sheet->createSheet();
        $monthlySheet->setTitle($this->translator->trans('SEPA Monat Kind'));

        for ($column = 1; $column <= 70; $column++) {
            $columnName = Coordinate::stringFromColumnIndex($column);
            $monthlySheet->setCellValue($columnName . '1', 'Collum ' . $column);
        }

        $row = 2;
        foreach ($sepa->getRechnungen() as $rechnung) {
            foreach ($rechnung->getKinder() as $kind) {
                $monthlySheet->setCellValue('A' . $row, $rechnung->getId());
                $monthlySheet->setCellValue('B' . $row, $rechnung->getStammdaten()->getKundennummerForOrg($sepa->getOrganisation()->getId()) ? $rechnung->getStammdaten()->getKundennummerForOrg($sepa->getOrganisation()->getId())->getKundennummer() : "");
                $monthlySheet->setCellValue('C' . $row, $kind->getVorname());
                $monthlySheet->setCellValue('D' . $row, $kind->getNachname());
                $row++;
            }
        }

        $sheetIndex = $sheet->getIndex(
            $sheet->getSheetByName('Worksheet')
        );
        $sheet->removeSheetByIndex($sheetIndex);

        $fileName = 'Sepa_Child_Monthly_ID' . $sepa->getId() . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        $excelWriter->save($tempFile);

        return $tempFile;
    }


}
