<?php

namespace App\Controller;

use App\Entity\LateRegistration;
use App\Entity\User;
use App\Form\Type\LateRegistrationType;
use App\Repository\LateRegistrationRepository;
use App\Service\LateRegistrationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LateRegistrationController extends AbstractController
{
    public function __construct(
        private LateRegistrationService $lateRegisterService,
        private LateRegistrationRepository $lateRegistrationRepository,
    )
    {
    }

    /**
     * @Route("/org_child/late_registration",name="late_registration",methods={"GET", "POST"})
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $stadt = $user?->getStadt();
        $schuljahre = $stadt?->getActives()?->toArray();

        $form = $this->createForm(LateRegistrationType::class, null, ['schuljahre' => $schuljahre]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var LateRegistration $lateRegistration */
            $lateRegistration = $form->getData();
            $lateRegistration->setStadt($stadt);
            $this->lateRegisterService->create($lateRegistration);

            // redirect to self to clear form data
            return $this->redirectToRoute('late_registration');
        }

        $history = $this->lateRegistrationRepository->findBy(['stadt' => $stadt]);

        return $this->render('late_registration/index.html.twig', [
            'form' => $form->createView(),
            'history' => $history,
        ]);
    }

    /**
     * @Route("/late_registration/{token}", name="late_registration_start", methods={"GET"})
     * @Entity("LateRegistration", expr="repository.findByStringToken(token)")
     */
    public function registerStart(Request $request, LateRegistration $lateRegistration): Response
    {
        if (!$this->lateRegisterService->isValid($lateRegistration, $request)) {
            throw $this->createAccessDeniedException();
        }
        $this->lateRegisterService->start($lateRegistration);

        $response = $this->redirectToRoute('workflow_start', ['slug' => $lateRegistration->getStadt()->getSlug()]);
        $response->headers->clearCookie('KindID');
        $response->headers->clearCookie('SecID');
        $response->headers->clearCookie('UserID');

        return $response;
    }
}
