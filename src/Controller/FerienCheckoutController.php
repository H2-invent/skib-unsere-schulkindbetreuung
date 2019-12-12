<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Stadt;
use App\Form\Type\PaymentType;
use App\Service\StamdatenFromCookie;
use Braintree\Gateway;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckoutController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/bezahlung",name="ferien_bezahlung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
        }

        $gateway =  new Gateway([
            'environment' => 'sandbox',
            'merchantId' => '65xmpcc6hh6khg5d',
            'publicKey' => 'wzkfsj9n2kbyytfp',
            'privateKey' => 'a153a39aaef70466e97773a120b95f91',
        ]);
        $clientToken = $gateway->clientToken()->generate();
        return $this->render('ferien_checkout/bezahlung.html.twig', array('stadt' => $stadt,'token'=>$clientToken,'form'=>$form->createView()));
    }

}
