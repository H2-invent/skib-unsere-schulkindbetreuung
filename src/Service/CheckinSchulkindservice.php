<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class CheckinSchulkindservice
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator,  EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public
    function checkin(Kind $kind, \DateTime $dateTime, Organisation $organisation)
    {

        $result['error'] = false;
        $result['errorText'] = $this->translator->trans('Kind erfolgreich eingecheckt');
        $result['checkinText'] = $this->translator->trans('Eingecheckt');
        $result['vorname']= $kind->getVorname();
        $result['nachname']= $kind->getNachname();
        $result['kurs']= '';

        return $result;
    }

}
