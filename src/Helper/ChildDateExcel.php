<?php

namespace App\Helper;

class ChildDateExcel
{
    private int $von;
    private int $bis;

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


}