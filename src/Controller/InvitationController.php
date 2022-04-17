<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /**
     * @Route("/login/invitation/accept/{token}", name="invitation_accept")
     */
    public function accept($token): Response
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('invitationToken' => $token));
        $user->setKeycloakId($this->getUser()->getKeycloakId());
        $oldUSer = $this->getUser();
        $oldUSer->setKeycloakId(null);
        $this->em->remove($oldUSer);
        $this->em->persist($user);
        $this->em->flush();
        $session = $this->get('session');
        $session->clear();
        $session->invalidate();
        return $this->redirectToRoute('dashboard');
    }
}
