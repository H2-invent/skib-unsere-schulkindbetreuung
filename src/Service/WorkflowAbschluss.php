<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;

use App\Entity\Stammdaten;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// <- Add this

class WorkflowAbschluss
{
    private $validator;
    private $formFactory;
    private $em;
    private $params;
    private $user;
   public function __construct(Security $security,ValidatorInterface $validator,FormFactoryInterface $formFactory,EntityManagerInterface $entityManager,ParameterBagInterface $params)
   {
        $this->validator = $validator;
       $this->formFactory = $formFactory;
       $this->em = $entityManager;
       $this->params = $params;
       $this->user = $security;
   }

    public
    function abschluss(Stammdaten $adresse,$kind)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }
        if ($adresse->getHistory() > 0) {// es gibt bereits eine alte Historie, diese bsitzt schon ein Fin
            $adresseOld = $this->em->getRepository(Stammdaten::class)->findOneBy(array('tracing' => $adresse->getTracing(), 'fin' => true));
            $adresseOld->setFin(false);
            $adresseOld->setEndedAt((clone $adresse->getCreatedAt())->modify('last day of this month'));
            $this->em->persist($adresseOld);
        }

        $adresse->setCreatedAt(new \DateTime());
        $adressCopy = clone $adresse;
        $adressCopy->setSaved(false);
        $adressCopy->setHistory($adressCopy->getHistory() + 1);
        $adressCopy->setSecCode(null);
        $adresse->setFin(true);
        $adresse->setSaved(true);
        $this->em->persist($adressCopy);
        foreach ($kind as $data) {
            if (!$data->getTracing()) {
                $data->setTracing(md5(uniqid('kind', true)));
            }
            if ($data->getHistory() > 0) {
                $kindOld = $this->em->getRepository(Kind::class)->findOneBy(array('fin' => true, 'tracing' => $data->getTracing()));
                $kindOld->setFin(false);
                $this->em->persist($kindOld);
            }
            $kindNew = clone $data;
            $kindNew->setHistory($kindNew->getHistory() + 1);
            $data->setSaved(true);
            $data->setFin(true);
            $this->em->persist($data);
            $kindNew->setEltern($adressCopy);

            foreach ($data->getZeitblocks() as $zb) {
                $zb->addKind($kindNew);
            }
            $this->em->persist($kindNew);
            foreach ($data->getBeworben() as $zb) {
                $kindNew->addBeworben($zb);
            }

        }
        $this->em->persist($adresse);
        $this->em->persist($adressCopy);
        $this->em->flush();


    }

}
