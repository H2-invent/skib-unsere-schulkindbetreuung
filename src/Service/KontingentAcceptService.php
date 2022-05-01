<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KontingentAcceptService
{
    private EntityManagerInterface $em;
    private AnmeldeEmailService $anmeldeEmailService;
    private TranslatorInterface $translator;
    public function __construct(EntityManagerInterface $entityManager, AnmeldeEmailService $anmeldeEmailService, TranslatorInterface $translator)
    {
        $this->em = $entityManager;
        $this->anmeldeEmailService = $anmeldeEmailService;
        $this->translator = $translator;
    }
    public function acceptKind(Zeitblock $zeitblock, Kind $kind){
        if (!in_array($kind,$zeitblock->getKinderBeworben()->toArray())){
            return false;
        }
        $kindWorkingcopy = $this->em->getRepository(Kind::class)->findActualWorkingCopybyKind($kind);
        $kind->addZeitblock($zeitblock);
        $kind->removeBeworben($zeitblock);
        $kindWorkingcopy->addZeitblock($zeitblock);
        $kindWorkingcopy->removeBeworben($zeitblock);
        $this->em->persist($kind);
        $this->em->persist($kindWorkingcopy);
        $this->em->flush();
        $this->beworbenCheck($kind);
        return true;
    }
    public function acceptAllkindOfZeitblock(Zeitblock $zeitblock){
        $kinder = $this->em->getRepository(Kind::class)->findBeworbenByZeitblock($zeitblock);
        foreach ($kinder as $data){
         $this->acceptKind($zeitblock,$data);
        }

    }
    public function beworbenCheck(Kind $kind){
        if(sizeof($kind->getBeworben()->toArray()) === 0){// es gibt keine beworbenen Zeitblöcke mehr. Das kind soll nun eine Buchungsbestätigung erhalten
            $this->anmeldeEmailService->sendEmail($kind, $kind->getEltern(), $kind->getZeitblocks()[0]->getSchule()->getStadt(), $this->translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihrers Kindes:'));
            //$anmeldeEmailService->setBetreff($translator->trans('Buchungsbestätigung der Schulkindbetreuung für ') . $data->getVorname() . ' ' . $data->getNachname());
            $this->anmeldeEmailService->send($kind, $kind->getEltern());
        }
        return false;
    }
}