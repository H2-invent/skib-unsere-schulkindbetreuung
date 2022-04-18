<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\InvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{

    /**
     * @Route("/login/invitation/accept/{token}", name="invitation_accept")
     */
    public function accept($token, InvitationService $invitationService): Response
    {

        $invitationService->acceptInvitation($token, $this->getUser());
        $session = $this->get('session');
        $session->clear();
        $session->invalidate();
        return $this->redirectToRoute('dashboard');
    }
}
