<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Repository\KindRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class WartelisteService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private KindRepository         $kindRepository,
        private ElternService          $elternService,
        private AnmeldeEmailService    $anmeldeEmailService,
        private TranslatorInterface    $translator,
        private WorkflowAbschluss      $workflowAbschluss,
        private MailerService          $mailerService,
        private Environment            $environment
    )
    {
    }

    public function addKindToWarteliste(Kind $kind, Zeitblock $zeitblock): bool
    {
        if (!$kind->getBeworben()->contains($zeitblock)) {
            throw new \Exception('Block does not match Child Slots');
        }
        $kind->addWarteliste($zeitblock);
        $kind->getBeworben()->removeElement($zeitblock);
        $kind->addMovedToWaiting($zeitblock);
        $this->entityManager->persist($kind);
        $this->entityManager->flush();
       $this->sendEmailForWartelisteAdding($kind,$zeitblock);
        return true;
    }
    public function sendEmailForWartelisteAdding(Kind $kind, Zeitblock $zeitblock): void
    {
        $content = $this->environment->render('email/childAddedToWarteliste.html.twig', array('kind' => $kind, 'block' => $zeitblock));
        $this->mailerService->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $this->elternService->getLatestElternFromChild($kind)->getEmail(),
            $this->translator->trans('Warteliste für Ihr Kind %vorname%', ['%vorname%' => $kind->getVorname()]),
            $content,
            $kind->getSchule()->getOrganisation()->getEmail());
    }
    public function removeKindFromWarteliste(Kind $kind, Zeitblock $zeitblock): bool
    {
        if (!$kind->getWarteliste()->contains($zeitblock)) {
            throw new \Exception('Block does not match waitinglist  Slots');
        }
        $kind->removeWarteliste($zeitblock);
        $this->entityManager->persist($kind);
        $this->entityManager->flush();
        return true;
    }

    public function acceptChildFromWaitingListForSpecificTime(Kind $kind, Zeitblock $zeitblock, $date): bool
    {
        $kindForTime = $this->kindRepository->findLatestKindForDate($kind, $date);
        $kindForTime->getZeitblocks();

        $kindNew = clone $kindForTime;


        foreach ($kindForTime->getZeitblocks() as $data) {
            $kindNew->addZeitblock($data);
        }
        foreach ($kindForTime->getBeworben() as $data) {
            $kindNew->addBeworben($data);
        }
        if ($kindNew->getZeitblocks()->contains($zeitblock)) {
            throw new \Exception('Block already accepted');
        }
        $kindNew->addZeitblock($zeitblock);
        $kindNew->setStartDate($date);


        $kind->removeWarteliste($zeitblock);
        $this->entityManager->persist($kind);

        $stammdatenNew = clone $kind->getEltern();

        $stammdatenNew->setStartDate(null);
        $stammdatenNew->setCreatedAt(new \DateTime());
        $kindNew->setEltern($stammdatenNew);
        foreach ($stammdatenNew->getKinds() as $data) {
            $stammdatenNew->removeKind($data);
        }
        $stammdatenNew->addKind($kindNew);


        $this->entityManager->persist($kindNew);
        $this->entityManager->persist($stammdatenNew);


        $this->entityManager->flush();

        $adresse = $this->elternService->getElternForSpecificTimeAndKind($kind, $date);
        $this->anmeldeEmailService->sendEmail($kindNew, $adresse, $kindNew->getSchule()->getStadt(), $this->translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihres Kindes:'));
        $this->anmeldeEmailService->send($kindNew, $adresse);

        return true;

    }
}