<?php

namespace App\Service;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Payment;
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

class FerienAbschluss
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public
    function abschlussFin(Stammdaten $adresse)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }
        $adresse->setFin(true);
        $this->em->persist($adresse);
        foreach ($adresse->getKinds() as $data) {
            $data->setFin(true);
            $this->em->persist($data);
        }
       $this->em->persist($adresse);
       $this->em->flush();
    }
    public
    function abschlussSave(Stammdaten $adresse)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }
        $adresse->setFin(true);
        $adresse->setSaved(true);
        //  $this->em->persist($adresse);
        foreach ($adresse->getKinds() as $data) {
            $data->setSaved(true);
            $data->setFin(true);
            $this->em->persist($data);
        }
        // $this->em->persist($adresse);
        //  $this->em->flush();
    }

}
