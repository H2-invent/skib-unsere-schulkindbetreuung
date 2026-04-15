<?php

namespace App\Service;

use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// <- Add this

class StamdatenFromCookie
{

    public function __construct(ValidatorInterface $validator,FormFactoryInterface $formFactory,private EntityManagerInterface $em,private ParameterBagInterface $params)
   {
        $this->validator = $validator;
       $this->formFactory = $formFactory;
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
