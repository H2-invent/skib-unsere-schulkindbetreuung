<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\Stadt;
use App\Service\CheckoutBraintreeService;
use App\Service\CheckoutPaymentService;
use App\Service\StamdatenFromCookie;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BrainTreeCheckoutController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/{slug}/ferien/braintree/prepare",name="ferien_braintree_start",methods={"Get"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentPrepareAction( Stadt $stadt, CheckoutBraintreeService $checkoutBraintreeService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $payment = $this->managerRegistry->getRepository(Payment::class)->findOneBy(array('uid'=>$request->get('id')));
        $checkoutBraintreeService->prepareBraintree($adresse, $request->getClientIp(),$payment);
            return $this->render('ferien_checkout/braintreePayment.html.twig', array('payment' => $payment, 'stadt' => $stadt));
    }

    /**
     * @Route("/ferien/braintree/recieveNonce",name="ferien_braintree_nonce",methods={"POST"})
     */
    public function paymentrecieveNonceAction(TranslatorInterface $translator, CheckoutPaymentService $checkoutPaymentService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        $braintree = $this->managerRegistry->getRepository(PaymentBraintree::class)->findOneBy(array('token' => $request->get('token')));
        $braintree->setNonce($request->get('nonce'));
        $braintree->getPayment()->setFinished(true)->setArtString('Credit Card/Paypal');
        $em = $this->managerRegistry->getManager();
        $em->persist($braintree);
        $em->flush();
        return new JsonResponse(array('error' => 0));
    }
}
