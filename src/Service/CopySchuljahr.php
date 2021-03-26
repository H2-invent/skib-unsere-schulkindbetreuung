<?php


namespace App\Service;


use App\Entity\Active;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;

class CopySchuljahr
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    function copyYear(Active $active)
    {
        $newYear = new Active();
        $newYear->setStadt($active->getStadt());
        $newYear->setAnmeldeEnde($active->getAnmeldeEnde());
        $newYear->setAnmeldeStart($active->getAnmeldeStart());
        $newYear->setBis($active->getBis());
        $newYear->setVon($active->getVon());
        foreach ($active->getBlocks() as $data) {
            $newBlock = new Zeitblock();
            $newBlock->setVon($data->getVon());
            $newBlock->setBis($data->getBis());
            $newBlock->setDeleted(false);
            $newBlock->setGanztag($data->getGanztag());
            $newBlock->setMax($data->getMax());
            $newBlock->setMin($data->getMin());
            $newBlock->setPreise($data->getPreise());
            $newBlock->setSchule($data->getSchule());
            $newBlock->setWochentag($data->getWochentag());
            $this->em->persist($newBlock);
            $newYear->addBlocks($newBlock);
        }
        $this->em->persist($newYear);
        $this->em->flush();

    }
}