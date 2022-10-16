<?php

namespace App\Service\ExcelExport;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Helper\ChildDateExcel;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateExcelDayService
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns the merged Time for a child for a given Day.
     * The Weekday mus be given from Monday to Sunday where Monday=0
     * @param Kind $kind
     * @param $weekday
     * @return string
     */
    public function getMergedTime(Kind $kind, $weekday): string
    {
        $res = '';
        $blocks = $kind->getBetreungsblocksReal();
        $cleanBlocks = array();
        foreach ($blocks as $data) {
            if ($data->getWochentag() === $weekday) {
                $cleanBlocks[] = $data;
            }
        }
        $excelBlocks = $this->createmergedDateTime($cleanBlocks);
        $tmp = array();
        foreach ($excelBlocks as $data) {
            $tmp[] =
                $data->getVonBisAsStringWithUhr($this->translator->trans('Uhr'));
        }
        return implode("\n", $tmp);
    }

    /**
     * @return ChildDateExcel[]
     */
    public function createmergedDateTime($zeitblocks)
    {
        $res = array();
        $helpberBlocks = array();
        foreach ($zeitblocks as $data) {
            $tmp = new ChildDateExcel();
            $tmp->setVon(intval($data->getVon()->format('H')) * 60 + intval($data->getVon()->format('i')));
            $tmp->setBis(intval($data->getBis()->format('H')) * 60 + intval($data->getBis()->format('i')));
            $helpberBlocks[] = $tmp;
        }

        usort($helpberBlocks, function (ChildDateExcel $a, ChildDateExcel $b) {
            return $a->getVon() > $b->getVon();
        });

        if (sizeof($helpberBlocks) === 0) {
            return $res;
        }


        $res[] = $helpberBlocks[0];

        foreach ($helpberBlocks as $key => $data) {
            if ($data->getVon() > $res[array_key_last($res)]->getBis()) {
                $res[] = $data;
            } else {
                if ($data->getBis() > $res[array_key_last($res)]->getBis()) {
                    $res[array_key_last($res)]->setBis($data->getBis());
                }
            }
        }
        return $res;
    }
}