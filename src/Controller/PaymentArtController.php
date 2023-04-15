<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Form\Type\OrganisationType;
use App\Form\Type\PaymentArtType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentArtController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/org_ferien_admin/payment/art", name="org_ferien_admin_payment_art")
     */
    public function index(ValidatorInterface $validator, Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $form = $this->createForm(PaymentArtType::class, $organisation);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $errors = $validator->validate($organisation);
            if(count($errors)== 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('dashboard',array('snack'=>$text));
            }

        }

        return $this->render('payment_art/index.html.twig',array('form' => $form->createView(),'errors'=>$errors));

    }
}
