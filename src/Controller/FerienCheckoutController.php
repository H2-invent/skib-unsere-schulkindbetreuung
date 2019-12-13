<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Stadt;
use App\Form\Type\PaymentType;
use App\Service\CheckoutSepaService;
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
    public function paymentAction(Request $request, ValidatorInterface $validator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie,CheckoutSepaService $checkoutSepaService)
    {
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,FerienController::BEZEICHNERCOOKIE);
        }
        $payment = new PaymentSepa();
        dump($adresse);
        if(!$adresse->getPaymentFerien()->isEmpty() && $adresse->getPaymentFerien()->toArray()[0]->getSepa()){
            $payment = $adresse->getPaymentFerien()->get(0)->getSepa();
        }
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $form->getData();
            $errors = $validator->validate($payment);
            $res = false;
            if(sizeof($errors)== 0){
              $res =  $checkoutSepaService->generateSepaPayment($adresse,$payment);
            }
            return $this->redirectToRoute('ferien_zusammenfassung',array('slug'=>$stadt->getSlug()));
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
    /**
     * @Route("/{slug}/ferien/bezahlung",name="ferien_bezahlung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentAction(Request $request, ValidatorInterface $validator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie,CheckoutSepaService $checkoutSepaService)
    {

    }
}
