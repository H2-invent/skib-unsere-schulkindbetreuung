<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class KontingentAcceptService
{
    private EntityManagerInterface $em;
    private AnmeldeEmailService $anmeldeEmailService;
    private TranslatorInterface $translator;
    private ElternService $elternService;

    public function __construct(EntityManagerInterface $entityManager, AnmeldeEmailService $anmeldeEmailService, TranslatorInterface $translator, ElternService $elternService)
    {
        $this->em = $entityManager;
        $this->anmeldeEmailService = $anmeldeEmailService;
        $this->translator = $translator;
        $this->elternService = $elternService;
    }

    public function acceptKind(Zeitblock $zeitblock, Kind $kind, $silent = false)
    {
        if (!in_array($kind, $zeitblock->getKinderBeworben()->toArray())) {
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
        if (!$silent) {
            $this->sendAcceptanceMailIfNoMoreBeworben($kind);
        }

        return true;
    }

    public function acceptAllZeitblockOfSpecificKind(Kind $kind, $silent = false):bool
    {


        $beworbenBlocks = $kind->getBeworben();
        foreach ($beworbenBlocks as $beworbenBlock) {
            $this->acceptKind($beworbenBlock, $kind, $silent);
        }
        return true;
    }

    public function acceptAllkindOfZeitblock(Zeitblock $zeitblock)
    {
        $kinder = $this->em->getRepository(Kind::class)->findBeworbenByZeitblock($zeitblock);
        foreach ($kinder as $data) {

            try {
                $this->acceptKind($zeitblock, $data);
            } catch (\Exception $exception) {

            }

        }

    }

    public function sendAcceptanceMailIfNoMoreBeworben(Kind $kind): void
    {
        if (count($kind->getBeworben()->toArray()) !== 0) {
            // Es gibt noch beworbene Zeitblöcke. Eine Buchungsbestätigung soll noch nicht geschickt werden.
            return;
        }

        $this->anmeldeEmailService->sendEmail($kind,
            $this->elternService->getLatestElternFromChild($kind),
            $this->getStadt($kind),
            $this->translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihrers Kindes:')
        );
        $this->anmeldeEmailService->send($kind, $this->elternService->getLatestElternFromChild($kind));
    }

    /**
     * @throws RuntimeException
     */
    public function getStadt(Kind $kind): Stadt
    {
        foreach ($kind->getZeitblocks() as $zeitblock) {
            $stadt = $zeitblock->getSchule()?->getStadt();
            if ($stadt !== null) {
                return $stadt;
            }
        }

        throw new RuntimeException("Could not find Stadt from Child Zeitblocks");
    }
}
