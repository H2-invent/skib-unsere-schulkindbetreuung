<?php

namespace App\Service\ExcelExport;

use App\Entity\Kind;
use App\Helper\ChildDateExcel;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateExcelDayService
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * Returns the merged Time for a child for a given Day.
     * The Weekday mus be given from Monday to Sunday where Monday=0.
     */
    public function getMergedTime(Kind $kind, $weekday, string $status): string
    {
        $blocks = match ($status) {
            'warteliste' => $kind->getRealWarteliste(),
            'beworben' => $kind->getRealBeworben(),
            default => $kind->getRealZeitblocks(),
        };

        $cleanBlocks = [];
        foreach ($blocks as $data) {
            if ($data->getWochentag() === $weekday) {
                $cleanBlocks[] = $data;
            }
        }
        $excelBlocks = $this->createmergedDateTime($cleanBlocks);
        $tmp = [];
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
        $res = [];
        $helpberBlocks = [];
        foreach ($zeitblocks as $data) {
            $tmp = new ChildDateExcel();
            $tmp->setVon(intval($data->getVon()->format('H')) * 60 + intval($data->getVon()->format('i')));
            $tmp->setBis(intval($data->getBis()->format('H')) * 60 + intval($data->getBis()->format('i')));
            $helpberBlocks[] = $tmp;
        }

        usort($helpberBlocks, fn (ChildDateExcel $a, ChildDateExcel $b) => $a->getVon() > $b->getVon());

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
