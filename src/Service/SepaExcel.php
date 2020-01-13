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
use App\Entity\Sepa;
use App\Entity\Stadt;
use App\Entity\Stammdaten;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

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
        $sepaSheet->setTitle($this->translator->trans('Rechnungen'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Vorname'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Nachname'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Betrag in €'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl der Kinder'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('IBAN'));
        $sepaSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('BIC'));
           $counter = 2;
        foreach ($sepa->getRechnungen() as $data) {
            $count = 0;
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getVorname());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getName());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getSumme());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getKinder()));
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getIban());
            $sepaSheet->setCellValue($alphas[$count++] . $counter, $data->getStammdaten()->getBic());
                    $counter++;
        }
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('Worksheet')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
        // Create a Temporary file in the system

        $fileName = 'Sepa_ID'.$sepa->getId().'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $this->writer->save($temp_file);

        // Return the excel file as an attachment
        return $temp_file;

    }



}
