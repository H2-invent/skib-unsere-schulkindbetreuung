<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Service\AnmeldeEmailService;
use App\Service\ChildEmailChangeService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeEmailController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator, private ManagerRegistry $managerRegistry)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/org_child/email_change", name="org_child_email_change")
     */
    public function index(TranslatorInterface $translator, Request $request, ChildEmailChangeService $childChangeEmailService)
    {
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Keine Berechtigung');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));
        }


        $form = $childChangeEmailService->form($kind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $input = $form->getData();
            if ($input['email'] !== $input['emailDoubleInput']) {
                $text = $this->translator->trans('Email Adressen stimmen nicht überein.');
                return $this->redirectToRoute('org_child_email_change', array('kind_id' => $kind->getId(), 'snack' => $text));
            }

            $childChangeEmailService->changeEmail($kind, $input, $this->getUser());

            $text = $translator->trans('Email adresse geändert');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));
        }
        return $this->render('child_change/email.html.twig', array('form' => $form->createView()));
    }
}
