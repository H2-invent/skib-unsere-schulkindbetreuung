<?php

namespace App\Service;

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
use Symfony\Component\Validator\Validator\ValidatorInterface;


// <- Add this

class StamdatenFromCookie
{

    private $em;
    private $params;
   public function __construct(ValidatorInterface $validator,FormFactoryInterface $formFactory,EntityManagerInterface $entityManager,ParameterBagInterface $params)
   {
        $this->validator = $validator;
       $this->formFactory = $formFactory;
       $this->em = $entityManager;
       $this->params = $params;
   }

    public
    function getStammdatenFromCookie(Request $request,$bezeichner='UserID')
    {
        if ($request->cookies->get($bezeichner)) {

            $cookie_ar = explode('.', $request->cookies->get($bezeichner));
            $hash = hash("sha256", $cookie_ar[0] . $this->params->get("secret"));

            if ($hash == $cookie_ar[1]) {
                $adresse = $this->em->getRepository(Stammdaten::class)->findActualStammdatenByUid($cookie_ar[0]);
                return $adresse;
            }

            return null;
        }
        return null;
    }

}
