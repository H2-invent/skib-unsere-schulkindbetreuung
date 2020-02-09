<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


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
            'text' => $this->translator->trans('Betreuungszeitfenster erfolgreich gespeichert'),
            'error' => 0,
            'blocks' => array(),
        );
        $blockRes = array(
            'id' => $block->getId(),
            'cardText' => $this->translator->trans('Gebucht'),
        );

        try {
            $result['preisUrl'] = $this->router->generate('loerrach_workflow_preis_einKind', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()));
            if ($block->getMin() || $block->getMax()) {
                $blockRes['kontingent'] = true;
            }

            if ($block->getMin() || $block->getMax()) {
                if (in_array($block, $kind->getBeworben()->toArray())) {
                    $kind->removeBeworben($block);
                    $blockRes['state'] = 2;
                    $blockRes['cardText'] = $this->translator->trans('Hier buchen');
                } else {
                    $kind->addBeworben($block);
                    $blockRes['state'] = 0;
                    $blockRes['cardText'] = $this->translator->trans('Angemeldet');
                }

            } else {
                if (in_array($block, $kind->getZeitblocks()->toArray())) {
                    $kind->removeZeitblock($block);
                    $blockRes['state'] = 2;
                    $blockRes['cardText'] = $this->translator->trans('Hier buchen');
                } else {
                    $kind->addZeitblock($block);
                    $blockRes['state'] = 1;
                    $blockRes['cardText'] = $this->translator->trans('Gebucht');
                }

            }

            $this->em->persist($kind);
            $this->em->flush();

            $blocks2 = $kind->getTageWithBlocks();
            $result['blocks'][] = $blockRes;
            if ($blocks2 < 2) {
                $result['text'] = $this->translator->trans('Bitte weiteres Betreuungszeitfenster auswählen (Es müssen mindestens zwei Tage ausgewählt werden)');
                $result['error'] = 2;
            }
        } catch (\Exeption $e) {
            $result['text'] = $this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        return $result;
    }

}
