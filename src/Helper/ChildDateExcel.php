<?php

namespace App\Helper;

class ChildDateExcel
{
    private int $von;
    private int $bis;
    private ?string $blockName = null;

    /**
     * @return int
     */
    public function getVon(): int
    {
        return $this->von;
    }

    /**
     * @param int $von
     */
    public function setVon(int $von): void
    {
        $this->von = $von;
    }

    /**
     * @return int
     */
    public function getBis(): int
    {
        return $this->bis;
    }

    /**
     * @param int $bis
     */
    public function setBis(int $bis): void
    {
        $this->bis = $bis;
    }

    public function getVonBisAsString(): string
    {
        return str_pad(floor($this->von / 60), 2, 0, STR_PAD_LEFT)
            . ':' . str_pad($this->von % 60, 2, 0, STR_PAD_LEFT)
            . ' - ' . str_pad(floor($this->bis / 60), 2, 0, STR_PAD_LEFT)
            . ':' . str_pad($this->bis % 60, 2, 0, STR_PAD_LEFT);
    }

    public function getVonBisAsStringWithUhr($uhrString): string
    {
        $res = $this->getVonBisAsString() . $uhrString;
        if ($this->getBlockName()) {
            $res .= " ({$this->blockName})";
        }

        return $res;
    }

    public function getBlockName(): ?string
    {
        return $this->blockName;
    }

    public function setBlockName(string $blockName): void
    {
        $this->blockName = $blockName;
    }


}
