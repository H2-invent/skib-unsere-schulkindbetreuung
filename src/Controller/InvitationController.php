<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\InvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InvitationController extends AbstractController
{

    /**
     * @Route("/login/invitation/accept/{token}", name="invitation_accept")
     */
    public function accept($token, InvitationService $invitationService, TokenStorageInterface $tokenStorage, Request $request): Response
    {

        $invitationService->acceptInvitation($token, $this->getUser());
        $request->getSession()->invalidate();
        $tokenStorage->setToken(null);
        return $this->redirectToRoute('dashboard');
    }
}
