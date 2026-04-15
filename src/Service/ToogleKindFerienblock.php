<?php

namespace App\Service;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class ToogleKindFerienblock
{


    public function __construct(private TranslatorInterface $translator, private EntityManagerInterface $em, private ElternService $elternService)
    {
    }

    public
    function toggleKind(Kind $kind, Ferienblock $block, $preisId)
    {
        $result = array(
            'text' => $this->translator->trans('Ferienprogramm erfolgreich gespeichert'),
            'error' => 0,
            'kontingent' => false,
            'cardText' => $this->translator->trans('Gebucht')
        );

        try {
            // gibt es bereits eine Paymentverbindung zwischen Den Eltern un der Org, dann lösche  diese.

            $payment = $this->em->getRepository(Payment::class)->findOneBy(array('organisation' => $block->getOrganisation(), 'stammdaten' => $this->elternService->getLatestElternFromChild($kind)));
            if ($payment) {
                $this->em->remove($payment);
                $this->em->flush();
            }

            if ($block->getMinAnzahl() || $block->getMaxAnzahl()) {
                $result['kontingent'] = true;
            }
            $kindFerienBlock = $this->em->getRepository(KindFerienblock::class)->findOneBy(array('kind' => $kind, 'ferienblock' => $block));

            if ($kindFerienBlock !== null) {

                $this->em->remove($kindFerienBlock);
                $result['cardText'] = $this->translator->trans('Hier buchen');
                $result['state'] = -1;
                $this->em->flush();
                $result['preis'] = number_format($kind->getFerienblockPreis(), 2, ',', '.') . '€';
                return $result;
            }

            $kindFerienBlock = new KindFerienblock();
            $kindFerienBlock->setKind($kind);
            $kindFerienBlock->setFerienblock($block);
            $kindFerienBlock->setPreis($block->getPreis()[$preisId]);
            $kindFerienBlock->setPreisId($preisId);
            $kindFerienBlock->setCheckinID(md5(uniqid()));


            if (null === $block->getMinAnzahl() || null === $block->getMaxAnzahl()) {
                // State: Ohne Kontingent direkt angemeldet (Gebucht)
                $kindFerienBlock->setState(10);
                $result['cardText'] = $this->translator->trans('Gebucht');
            } else {

                $aktuell = sizeof($block->getKindFerienblocksGesamt()) + 1;

                // Fall 1: aktuell ist kleiner  max und kind automatisch hinzugefügt
                //==> dann state auf 10 da das Kind gebuct ist.
                if ($aktuell <= $block->getMaxAnzahl() && $block->getModeMaximal() === false) {
                    $kindFerienBlock->setState(10);
                    $result['cardText'] = $this->translator->trans('Gebucht');
                } // Fall 2:  aktuell ist kleiner  max und kind manuel hinzugefügt
                elseif ($aktuell <= $block->getMaxAnzahl() && $block->getModeMaximal() === true) {
                    $kindFerienBlock->setState(0);
                    $result['cardText'] = $this->translator->trans('Angemeldet');
                } // Fall 3:  aktuell ist größer max und kind warteliste aktiv
                elseif ($aktuell > $block->getMaxAnzahl() && $block->getWarteliste() === true) {
                    $kindFerienBlock->setState(0);
                    $result['cardText'] = $this->translator->trans('Angemeldet');
                } // Fall 4: aktuell ist größer max und kind warteliste deaktiviert
                elseif ($aktuell > $block->getMaxAnzahl() && $block->getWarteliste() === false) {
                    $result['state'] = -1;
                    $result['cardText'] = $this->translator->trans('Hier buchen');
                    $result['text'] = $this->translator->trans('Es sind keine Plätze mehr verfügbar.');
                    $result['preis'] = number_format($kind->getFerienblockPreis(), 2, ',', '.') . '€';
                    return $result;
                }

            }
            $this->em->persist($kindFerienBlock);
            $this->em->flush();

        } catch (\Exception) {
            $result['text'] = $this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        $result['state'] = $kindFerienBlock->getState();
        $result['preis'] = number_format($kind->getFerienblockPreis(), 2, ',', '.') . '€';
        return $result;
    }

}
