<?php

namespace App\Service;

use App\Entity\KindFerienblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class CheckinFerienService
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator,  EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public
    function checkin($checkinID)
    {
        $kindFerienBlock = $this->em->getRepository(KindFerienblock::class)->findOneBy(array('checkinID' => $checkinID));
        $today = new \DateTime('today');

        $result['error'] = false;
        $result['errorText'] = $this->translator->trans('Kind erfolgreich eingecheckt');
        $result['checkinText'] = $this->translator->trans('Eingecheckt');

        if ($kindFerienBlock === null) {
            $result['error'] = true;
            $result['errorText'] = $this->translator->trans('Ticket ist falsch oder ungültig');

            return $result;
        }

        $startdateFerienblock = $kindFerienBlock->getFerienblock()->getStartDate();
        $enddateFerienblock = $kindFerienBlock->getFerienblock()->getEndDate();

        if ($today < $startdateFerienblock && $today > $enddateFerienblock) {
            $result['error'] = true;
            $result['errorText'] = $this->translator->trans('Dieses Ticket ist an einem anderen Tag gültig');
        }

        $result['checkinDate'] = $today->format('Y-m-d');
        $status = $kindFerienBlock->getCheckinStatus() !== null ? $kindFerienBlock->getCheckinStatus() : array();

        if (in_array($result['checkinDate'], $status)) {
            $result['error'] = true;
            $result['errorText'] = $this->translator->trans('Kind für den heutigen Tag bereits eingecheckt');
        }

        if ($kindFerienBlock->getState() >= 20) {
            $result['error'] = true;
            $result['errorText'] = $this->translator->trans('Das Ticket wurde Storniert und kann nicht eingcheckt werden');
        }
        if ($result['error'] === false) {
            $status[] = $result['checkinDate'];
            $kindFerienBlock->setCheckinStatus($status);

            $this->em->persist($kindFerienBlock);
            $this->em->flush();
        }
        return $result;
    }

}
