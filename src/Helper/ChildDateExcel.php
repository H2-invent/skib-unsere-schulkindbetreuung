<?php

namespace App\Helper;

class ChildDateExcel
{
    private int $von;
    private int $bis;

    public function getVon(): int
    {
        return $this->von;
    }

    public function setVon(int $von): void
    {
        $this->von = $von;
    }

    public function getBis(): int
    {
        return $this->bis;
    }

    public function setBis(int $bis): void
    {
        $this->bis = $bis;
    }

    public function getVonBisAsString()
    {
        return str_pad(floor($this->von / 60), 2, 0, STR_PAD_LEFT)
         . ':' . str_pad($this->von % 60, 2, 0, STR_PAD_LEFT)
         . ' - ' . str_pad(floor($this->bis / 60), 2, 0, STR_PAD_LEFT)
         . ':' . str_pad($this->bis % 60, 2, 0, STR_PAD_LEFT);
    }

    public function getVonBisAsStringWithUhr($uhrString)
    {
        return $this->getVonBisAsString() . $uhrString;
    }
}
