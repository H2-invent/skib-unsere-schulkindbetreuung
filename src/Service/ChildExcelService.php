<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Controller\LoerrachWorkflowController;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\ExcelExport\CreateExcelDayService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Composer\Autoload\includeFile;

class ChildExcelService
{
    private $spreadsheet;
    private $writer;
    private $translator;
    private $tokenStorage;
    private $createExcelDayService;
    private Stadt $stadt;
    private $alphas;
    private ElternService $elternService;
    private BerechnungsService $berechnungsService;
    public function __construct(TranslatorInterface $translator, TokenStorageInterface $tokenStorage, CreateExcelDayService $createExcelDayService, ElternService $elternService, BerechnungsService $berechnungsService)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->createExcelDayService = $createExcelDayService;
        $this->elternService = $elternService;
        $this->berechnungsService = $berechnungsService;
    }

    public function generateExcel($kinder, Stadt $stadt)
    {
        $beruflicheSituation = new LoerrachWorkflowController($this->translator);
        $this->stadt = $stadt;
        $this->alphas = $this->createColumnsArray('ZZ');
        $count = 0;
        $this->writeSpreadsheet($kinder);
        $this->writeSpreadsheet($kinder, $this->translator->trans('Montag'), [0]);
        $this->writeSpreadsheet($kinder, $this->translator->trans('Dienstag'), [1]);
        $this->writeSpreadsheet($kinder, $this->translator->trans('Mittwoch'), [2]);
        $this->writeSpreadsheet($kinder, $this->translator->trans('Donnerstag'), [3]);
        $this->writeSpreadsheet($kinder, $this->translator->trans('Freitag'), [4]);

        $this->spreadsheet->getSheetByName('Kinder');
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('Worksheet')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
        $this->spreadsheet->setActiveSheetIndex(0);


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

    /**
     * @param Kind[] $kinder
     * @param $title
     * @return void
     */
    public function writeSpreadsheet($kinder, $title = 'Kinder', $weekdays = [0, 1, 2, 3, 4])
    {

        $beruflicheSituation = new LoerrachWorkflowController($this->translator);
        $alphas = $this->createColumnsArray('ZZ');
        $count = 0;
        $kindSheet = $this->spreadsheet->createSheet();
        $kindSheet->setTitle($this->translator->trans($title));
        $this->writeHeaderLine($kindSheet, $weekdays);


        $counter = 2;
        foreach ($kinder as $data) {
            if ($this->checkIfChildhasBlockOnDayOfArray($data, $weekdays)) {
                $eltern = $this->elternService->getElternForSpecificTimeAndKind($data,new \DateTime());
                $count = 0;
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getVorname());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getNachname());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, ($data->getGeburtstag()->diff($eltern->getCreatedAt()))->y);
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getKlasseString());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getArt());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getArtString());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getSchule()->getName());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getVorname() . ' ' . $eltern->getName());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getStrasse());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getAdresszusatz());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getPlz());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getStadt());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getAlleinerziehend());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, array_flip($beruflicheSituation->beruflicheSituation)[$eltern->getBeruflicheSituation()]);
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getPhoneNumber());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getNotfallName());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getNotfallkontakt());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getAbholberechtigter());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getMedikamente());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getAllergie());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getMasernImpfung());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getBemerkung());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getGluten());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getSchweinefleisch());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getLaktose());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getSonnencreme());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getAusfluege());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getAlleineHause());
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getFotos());

                if (in_array(0, $weekdays)) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->createExcelDayService->getMergedTime($data, 0));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, '');
                }
                if (in_array(1, $weekdays)) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->createExcelDayService->getMergedTime($data, 1));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, '');
                }
                if (in_array(2, $weekdays)) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->createExcelDayService->getMergedTime($data, 2));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, '');
                }
                if (in_array(3, $weekdays)) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->createExcelDayService->getMergedTime($data, 3));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, '');
                }
                if (in_array(4, $weekdays)) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->createExcelDayService->getMergedTime($data, 4));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, '');
                }
                if ($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_VIEW_NOTICE')) {
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, strip_tags($data->getInternalNotice()));

                }
                $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getEmail());

                if ($this->stadt->getSettingsweiterePersonenberechtigte()) {
                    $persBErechtiger = array();
                    foreach ($eltern->getPersonenberechtigters() as $data3) {
                        $persBErechtiger[] = $data3->getVorname() . " " . $data3->getNachname() . "\n" . $data3->getStrasse() . "\n" . $data3->getPlz() . ' ' . $data3->getStadt() . "\n" . ' Tel: ' . $data3->getPhone() . "\n" . ' Notfallkotakt: ' . $data3->getNotfallkontakt();
                    }
                    $kindSheet->setCellValue($this->alphas[$count] . $counter, implode("\n\n", $persBErechtiger));
                    $kindSheet->getStyle($this->alphas[$count++] . $counter)->getAlignment()->setWrapText(true);
                }


                if ($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_ACCOUNTING')) {

                    if ($this->stadt->getSettingKinderimKiga()) {
                        if ($eltern->getKinderImKiga()) {
                            $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getKigaOfKids());
                        } else {
                            $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->translator->trans('Nein'));
                        }
                    }


                    if ($this->stadt->getSettingGehaltsklassen()) {
                        $kindSheet->setCellValue($this->alphas[$count++] . $counter, $data->getSchule()->getStadt()->getGehaltsklassen()[$eltern->getEinkommen()]);
                    }

                    if ($this->stadt->getSettingsAnzahlKindergeldempfanger()) {
                        $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getAnzahlKindergeldempfanger());
                    }
                    if ($this->stadt->getSettingsSozielHilfeEmpfanger()) {
                        $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getSozialhilfeEmpanger());
                    }

                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getCreatedAt()->format('d.m.Y'));
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getKundennummerForOrg($data->getSchule()->getOrganisation()->getId()) ? $eltern->getKundennummerForOrg($data->getSchule()->getOrganisation()->getId())->getKundennummer() : "");
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getIban());
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getBic());
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $eltern->getKontoinhaber());
                    $kindSheet->setCellValue($this->alphas[$count++] . $counter, $this->berechnungsService->getPreisforBetreuung($data));
                    if ($this->stadt->getSettingsEingabeDerGeschwister()) {
                        $geschwister = '';
                        foreach ($eltern->getGeschwisters() as $gesch) {
                            $geschwister .= ($gesch->getVorname() . ' ' . $gesch->getNachname() . "\n" . $gesch->getGeburtsdatum()->format('d.m.Y') . "\n");
                            foreach ($gesch->getFile() as $doc) {
                                $geschwister .= $doc->getOriginalName() . "\n";
                            }
                            $geschwister .= "\n";
                        }
                        $kindSheet->setCellValue($this->alphas[$count] . $counter, $geschwister);
                        $kindSheet->getStyle($this->alphas[$count++] . $counter)->getAlignment()->setWrapText(true);
                    }
                }
                $counter++;
            }
        }

    }

    public function writeHeaderLine($kindSheet, $weekdays)
    {
        $count = 0;
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Vorname'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Nachname'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Alter'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Klasse'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Typ Numerisch'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Typ'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Schule'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Erziehungsberechtigter'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Straße'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Adresse'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('PLZ'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Ort'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Alleinerziehend'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Berufliche Situation des Erziehungsberechtigten'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Telefonnummer'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Notfallkontakt'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Notfallnummer'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Abholung durch'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Medikamente'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Allergien'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Masernimpfung'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Bemerkung'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Gluten intolerant'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Kein Schweinefleisch'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Laktose intolerant'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Darf mit Sonnencreme eingecremt werden'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Darf an Ausflügen teilnehmen'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Darf alleine nach Hause'));
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Fotos dürfen veröffentlicht werden'));

        if (in_array(0, $weekdays)) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Montag'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('AG'));
        }
        if (in_array(1, $weekdays)) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Dienstag'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('AG'));
        }
        if (in_array(2, $weekdays)) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Mittwoch'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('AG'));
        }
        if (in_array(3, $weekdays)) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Donnerstag'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('AG'));
        }
        if (in_array(4, $weekdays)) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Freitag'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('AG'));
        }

        if ($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_VIEW_NOTICE')) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Notizen'));
        }
        $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('E-Mail Adresse'));
        if ($this->stadt->getSettingsweiterePersonenberechtigte()) {
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Weitere personenberechtigte Personen'));
        }

        if ($this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ORG_ACCOUNTING')) {
            //here are the configurable inputs from the parents
            if ($this->stadt->getSettingKinderimKiga()) {
                $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Kinder im KiGa'));
            }

            if ($this->stadt->getSettingGehaltsklassen()) {
                $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Brutto Haushaltseinkommen pro Monat'));
            }
            if ($this->stadt->getSettingsAnzahlKindergeldempfanger()) {
                $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Anzahl der Kindergeldberechtigten Kinder im Haushalt'));
            }
            if ($this->stadt->getSettingsSozielHilfeEmpfanger()) {
                $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Ist Sozielhilfeempfänger'));
            }

            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Angemeldet am:'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Kundennummer'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('IBAN'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('BIC'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Kontoinhaber'));
            $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Gebühr pro Monat für gebuchte Betreuung'));
            if ($this->stadt->getSettingsEingabeDerGeschwister()) {
                $kindSheet->setCellValue($this->alphas[$count++] . '1', $this->translator->trans('Geschwister'));
            }
        }
    }

    public function checkIfChildhasBlockOnDayOfArray(Kind $kind, $weekdays)
    {
        $res = false;
        foreach ($kind->getBetreungsblocksReal() as $data) {
            if (in_array($data->getWochentag(), $weekdays)) {
                return true;
            };
        }
        return false;
    }
}
