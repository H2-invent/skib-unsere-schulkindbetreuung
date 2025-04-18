<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Service\AnmeldeEmailService;
use App\Service\ChildSchoolYearChangeService;
use App\Service\ChildEmailChangeService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeSchoolyearController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator, private ManagerRegistry $managerRegistry)
    {
        $this->translator = $translator;
    }


    /**
     * @Route("/org_child/schoolyear_change", name="org_child_shoolyear_change")
     */
    public function index(TranslatorInterface $translator, Request $request, AnmeldeEmailService $anmeldeEmailService, ChildSchoolYearChangeService $childChoolYearChangeService)
    {
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Keine Berechtigung');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));
        }


        $form = $childChoolYearChangeService->form($kind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $input = $form->getData();
            $childChoolYearChangeService->changeSchoolyear($kind, $input, $this->getUser());

            $text = $translator->trans('Schuljahr geändert');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));
        }
        return $this->render('child_change/schoolYear.html.twig', array('form' => $form->createView()));
    }
}
