<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class ToogleKindBlockSchulkind
{


    private $em;
    private $translator;
    private $router;


    public function __construct(RouterInterface $router, TranslatorInterface $translator, Security $security, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->user = $security;
        $this->translator = $translator;
        $this->router = $router;


    }

    public
    function toggleKind(Stadt $stadt, Kind $kind, Zeitblock $block)
    {
        $result = array(
            'snack'=>array(),
            'text' => $this->translator->trans('Betreuungszeitfenster erfolgreich gespeichert'),
            'error' => 0,
            'blocks' => array(),
        );

        try {
            $result['blocks'] = $this->toggleBlock($kind, $block);
            $blocks2 = $kind->getTageWithBlocks();

            if ($blocks2 < $stadt->getMinDaysperWeek()) {
                $result['snack'][] = array('type'=>'warning','text'=>$this->translator->trans('Bitte weiteres Betreuungszeitfenster auswählen (Es müssen mindestens %d% Tage ausgewählt werden)',array('%d%'=>$stadt->getMinDaysperWeek())));
                $result['text'] = $this->translator->trans('Bitte weiteres Betreuungszeitfenster auswählen (Es müssen mindestens %d% Tage ausgewählt werden)',array('%d%'=>$stadt->getMinDaysperWeek()));
                $result['error'] = 2;
            }else{
                $result['preisUrl'] = $this->router->generate('loerrach_workflow_preis_einKind', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()));

            }
        } catch (\Exeption $e) {
            $result['snack'][] = array('type'=>'error','text'=>$this->translator->trans('Fehler. Bitte versuchen Sie es erneut.'));
            $result['error'] = 1;
        }
        if(sizeof($result['blocks'])>1){
            $result['snack'][]=array('type'=>'info','text'=>$this->translator->trans('Es wurden weitere Blöcke bearbeitet, da keine unbetreuten Zeiten in der Tagesbetreuung vorhanden sein dürfen'));
        }
        return $result;
    }

    private function toggleBlock(Kind $kind, Zeitblock $block)
    {

        $res = array();
        if ($block->getMin() || $block->getMax()) {
            if (in_array($block, $kind->getBeworben()->toArray())) {
                $res = $this->blockDelete($kind, $block);

            } else {
                $res = $this->blockAdd($kind, $block);
            }

        } else {
            if (in_array($block, $kind->getZeitblocks()->toArray())) {
                $res = $this->blockDelete($kind, $block);

            } else {
                $res = $this->blockAdd($kind, $block);

            }

        }

        return $res;
    }

    private function blockDelete(Kind $kind, Zeitblock $block): array
    {
        $state = null;
        $blockRes = array(
            'id' => $block->getId(),
            'cardText' => $this->translator->trans('Gebucht'),
        );

        if ($block->getMin() || $block->getMax()) {
            $blockRes['kontingent'] = true;
            if (in_array($block, $kind->getBeworben()->toArray())) {
                $kind->removeBeworben($block);
                $blockRes['state'] = 2;
                $state = 2;
                $blockRes['cardText'] = $this->translator->trans('Hier buchen');
            }
        } else {
            if (in_array($block, $kind->getZeitblocks()->toArray())) {
                $kind->removeZeitblock($block);
                $blockRes['state'] = 2;
                $state = 2;
                $blockRes['cardText'] = $this->translator->trans('Hier buchen');
            }
        }

        if ($state === null) {
            return array();
        } else {
            $this->em->persist($kind);
            $this->em->flush();
        }
        $res = array();
        $res[] = $blockRes;
        foreach ($block->getNachfolger() as $data) {
            $tmp = $this->blockDelete($kind, $data);
            $res = array_merge($res, $tmp);
        }
        return $res;
    }

    private function blockAdd(Kind $kind, Zeitblock $block): array
    {
        $state = null;
        $blockRes = array(
            'id' => $block->getId(),
            'cardText' => $this->translator->trans('Gebucht'),
        );
        if ($block->getMin() || $block->getMax()) {
            $blockRes['kontingent'] = true;
            if (!in_array($block, $kind->getBeworben()->toArray())) {
                $kind->addBeworben($block);
                $blockRes['state'] = 0;
                $state = 0;
                $blockRes['cardText'] = $this->translator->trans('Angemeldet');
            }

        } else {
            if (!in_array($block, $kind->getZeitblocks()->toArray())) {
                $kind->addZeitblock($block);
                $blockRes['state'] = 1;
                $state = 1;
                $blockRes['cardText'] = $this->translator->trans('Gebucht');
            }

        }

        if ($state === null) {
            return array();
        } else {
            $this->em->persist($kind);
            $this->em->flush();
        }
        $res = array();
        $res[] = $blockRes;
        foreach ($block->getVorganger() as $data) {
            $tmp = $this->blockAdd($kind, $data);
            $res = array_merge($res, $tmp);
        }
        return $res;
    }
}
