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
    private $formBuilder;
    private $twig;
    private $mailer;

    public function __construct(MailerService $mailerService, Environment $twig, FormFactoryInterface $formBuilder, RouterInterface $router, TranslatorInterface $translator, Security $security, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->user = $security;
        $this->translator = $translator;
        $this->router = $router;
        $this->formBuilder = $formBuilder;
        $this->twig = $twig;
        $this->mailer = $mailerService;
    }

    public
    function toggleKind(Stadt $stadt, Kind $kind, Zeitblock $block)
    {
        $result = array(
            'text' => $this->translator->trans('Betreuungszeitfenster erfolgreich gespeichert'),
            'error' => 0,
            'kontingent' => false,
            'cardText' => $this->translator->trans('Gebucht')
        );
        try {
            $result['preisUrl'] = $this->router->generate('loerrach_workflow_preis_einKind', array('slug'=>$stadt->getSlug(),'kind_id' => $kind->getId()));

            if ($block->getMin() || $block->getMax()) {
                $result['kontingent'] = true;
                $result['cardText'] = $this->translator->trans('Angemeldet');
            }
            if ($block->getMin() || $block->getMax()) {
                if (in_array($block, $kind->getBeworben()->toArray())) {
                    $kind->removeBeworben($block);
                } else {
                    $kind->addBeworben($block);
                }
            } else {
                if (in_array($block, $kind->getZeitblocks()->toArray())) {
                    $kind->removeZeitblock($block);
                } else {
                    $kind->addZeitblock($block);
                }
            }


            $this->em->persist($kind);
            $this->em->flush();

            $blocks2 = $kind->getTageWithBlocks();

            if ($blocks2 < 2) {
                $result['text'] = $this->translator->trans('Bitte weiteren Betreuungszeitfenster auswählen (Mindestens zwei Tage müssen ausgewählt werden)');
                $result['error'] = 2;
            }
        } catch (\Exeption $e) {
            $result['text'] = $this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        return $result;
    }

}
