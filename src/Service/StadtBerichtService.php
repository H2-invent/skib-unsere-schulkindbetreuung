<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Contracts\Translation\TranslatorInterface;

class StadtBerichtService
{
    private $spreadsheet;

    private $translator;
    private $beruflicheSituationString;
    private ChildInBlockService $childInBlockService;
    private ElternService $elternService;
    private $em;
    public function __construct(TranslatorInterface $translator, LoerrachWorkflowController $loerrachWorkflowController, ChildInBlockService $childInBlockService, ElternService $elternService,EntityManagerInterface $entityManager)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->translator = $translator;
        $this->beruflicheSituationString = array_flip($loerrachWorkflowController->beruflicheSituation);
        $this->childInBlockService = $childInBlockService;
        $this->elternService = $elternService;
        $this->em = $entityManager;
    }

    public function generateExcel($blocks, $kinder, $eltern, Stadt $stadt)
    {

        $writer = new Xlsx($this->spreadsheet);


        $alphas = range('a', 'z');

        // hier wird das block sheet erstellt
        $blocksheet = $this->spreadsheet->createSheet();
        $blocksheet->setTitle($this->translator->trans('Betreuungszeitfenster'));
        $count = 0;
        $blocksheet->setCellValue($alphas[$count++] . '1', 'Block_ID');
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Von'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Bis'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Wochentag'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Wochentag Numerisch'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Typ'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Typ Numerisch'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Preise'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Schuljahr Anfang'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Schuljahr Ende'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl Kinder'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Organisation'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Schule'));
        $blocksheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Deaktiviert'));
        $counter = 2;
        foreach ($blocks as $data) {
            $count = 0;

            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getId());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getVon()->format('H:i'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getBis()->format('H:i'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getWochentag());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $this->translator->trans($data->getWochentagString()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $this->translator->trans($data->getGanztagString()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getGanztag());
            $blocksheet->setCellValue($alphas[$count++] . $counter, json_encode($data->getPreise()));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getActive()->getVon()->format('d.m.Y'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getActive()->getBis()->format('d.m.Y'));
            $blocksheet->setCellValue($alphas[$count++] . $counter, sizeof($this->childInBlockService->getCurrentChildAndFuturerChildOfZeitblock($data,$data->getVon())));
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getOrganisation()->getName());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getName());
            $blocksheet->setCellValue($alphas[$count++] . $counter, $data->getDeleted());
            $counter++;
        }
        $kindSheet = $this->spreadsheet->createSheet();
        $kindSheet->setTitle($this->translator->trans('Kinder'));
        $kindSheet->setCellValue('A1', 'Kind_ID');
        $kindSheet->setCellValue('B1', $this->translator->trans('Alter'));
        $kindSheet->setCellValue('C1', $this->translator->trans('Klasse'));
        $kindSheet->setCellValue('D1', $this->translator->trans('Typ Numerisch'));
        $kindSheet->setCellValue('E1', $this->translator->trans('Typ'));
        $kindSheet->setCellValue('F1', $this->translator->trans('Schule'));
        $kindSheet->setCellValue('G1', $this->translator->trans('Erziehungsberechtigter'));
        $counter = 2;
        foreach ($kinder as $data) {
            $count = 0;
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getId());
            $kindSheet->setCellValue($alphas[$count++] . $counter, ($data->getGeburtstag()->diff($data->getEltern()->getCreatedAt()))->y);
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getKlasseString());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArt());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getArtString());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $data->getSchule()->getName());
            $kindSheet->setCellValue($alphas[$count++] . $counter, $this->elternService->getElternForSpecificTimeAndKind($data,$this->em->getRepository(Active::class)->findSchuljahrFromCity($data->getSchule()->getStadt(),$data->getStartDate())->getBis())->getId());
            $counter++;
        }
        $elternSheet = $this->spreadsheet->createSheet();
        $elternSheet->setTitle($this->translator->trans('Erziehungsberechtigter'));
        $count = 0;
        $elternSheet->setCellValue($alphas[$count++] . '1', 'Erziehungsberechtigter_ID');
        $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Berufliche Situation'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Berufliche Situation numerisch'));
        $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Alleinerziehend'));
        if ($stadt->getSettingKinderimKiga()) {
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Kinder im KiGa'));
        }
        if ($stadt->getSettingGehaltsklassen()) {
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Einkommensgruppe numerisch'));
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Einkommensgruppe'));
        }
        if ($stadt->getSettingsAnzahlKindergeldempfanger()) {
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl der Kindergeldberechtigten Kinder im Haushalt'));
        }
        if ($stadt->getSettingsSozielHilfeEmpfanger()) {
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Ist Sozielhilfeempfänger'));
        }
        if ($stadt->getSettingsweiterePersonenberechtigte()) {
            $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl weitere personenberechtigte Personen'));
        }
        $elternSheet->setCellValue($alphas[$count++] . '1', $this->translator->trans('Anzahl an Kindern in der Schulkindbetreuung'));
        $counter = 2;

        foreach ($eltern as $data) {
            $count = 0;
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getId());

            $elternSheet->setCellValue($alphas[$count++] . $counter, $this->beruflicheSituationString[$data->getBeruflicheSituation()]);
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getBeruflicheSituation());
            $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getAlleinerziehend());
            if ($stadt->getSettingKinderimKiga()) {
                $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getKinderImKiga());
            }
            if ($stadt->getSettingGehaltsklassen()) {
                $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getEinkommen());
                $elternSheet->setCellValue($alphas[$count++] . $counter, $stadt->getGehaltsklassen()[$data->getEinkommen()]);
            }
            if ($stadt->getSettingsAnzahlKindergeldempfanger()) {
                $elternSheet->setCellValue($alphas[$count++] . $counter,  $data->getSozialhilfeEmpanger());

            }
            if ($stadt->getSettingsSozielHilfeEmpfanger()) {

                $elternSheet->setCellValue($alphas[$count++] . $counter, $data->getSozialhilfeEmpanger());
            }
            if ($stadt->getSettingsweiterePersonenberechtigte()) {
                $elternSheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getPersonenberechtigters()));
            }

            $elternSheet->setCellValue($alphas[$count++] . $counter, sizeof($data->getKinds()));
            $counter++;
        }
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('Worksheet')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
        // Create a Temporary file in the system
        $fileName = 'Bericht.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);


        return $temp_file;

    }


}
