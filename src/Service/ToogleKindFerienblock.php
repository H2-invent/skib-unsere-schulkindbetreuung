<?php

namespace App\Service;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
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

class ToogleKindFerienblock
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator,  EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
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
            //Include Parents in this route

            if ($block->getMinAnzahl() || $block->getMaxAnzahl()) {
                $result['kontingent'] = true;
                $result['cardText'] = $this->translator->trans('Angemeldet');
            }
            $kindFerienBlock = $this->em->getRepository(KindFerienblock::class)->findOneBy(array('kind' => $kind, 'ferienblock' => $block));

            if ($kindFerienBlock !== null) {
                $this->em->remove($kindFerienBlock);
            } else {
                if ($block->getMinAnzahl() || $block->getMaxAnzahl()) {

                    $kindFerienBlock = new KindFerienblock();
                    $kindFerienBlock->setKind($kind);
                    $kindFerienBlock->setFerienblock($block);
                    $kindFerienBlock->setState(0);
                    $kindFerienBlock->setPreis($block->getPreis()[$preisId]);
                    $this->em->persist($kindFerienBlock);

                } else {

                    $kindFerienBlock = new KindFerienblock();
                    $kindFerienBlock->setKind($kind);
                    $kindFerienBlock->setFerienblock($block);
                    $kindFerienBlock->setState(0);
                    $kindFerienBlock->setPreis($block->getPreis()[$preisId]);
                    $this->em->persist($kindFerienBlock);

                }
            }

            $this->em->flush();

        } catch (\Exception $e) {
            $result['text'] = $this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        return $result;
    }

}
