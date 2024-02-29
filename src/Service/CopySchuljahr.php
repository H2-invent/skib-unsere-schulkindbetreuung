<?php


namespace App\Service;


use App\Entity\Active;
use App\Entity\Zeitblock;
use App\Repository\ZeitblockRepository;
use Doctrine\ORM\EntityManagerInterface;

class CopySchuljahr
{


    public function __construct(
        private ZeitblockRepository $zeitblockRepository,
        private EntityManagerInterface $em
    )
    {

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
            if (!$data->getDeleted()){
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
                $newBlock->setCloneOf($data);
                $this->em->persist($newBlock);
                $newYear->addBlocks($newBlock);
            }

        }
        $this->em->persist($newYear);

        $this->em->flush();
        $this->addVorganger($newYear);

    }
    function addVorganger(Active $active){
        foreach ($active->getBlocks() as $data){
            foreach ($data->getCloneOf()->getVorganger() as $vorganger){
                $newVorganger = $this->zeitblockRepository->findOneBy(array('cloneOf'=>$vorganger,'active'=>$active));
                dump($newVorganger->getId());
                $data->addVorganger($newVorganger);
            }
            $this->em->persist($data);
        }
        $this->em->flush();
    }
}