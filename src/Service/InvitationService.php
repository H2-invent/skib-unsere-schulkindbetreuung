<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class InvitationService
{
    private EntityManagerInterface $em;
    private Environment $environment;
    private MailerService $mailerService;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, Environment $environment, MailerService $mailerService, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $entityManager;
        $this->environment = $environment;
        $this->mailerService = $mailerService;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public function inviteNewUser(User $user, User $creator)
    {
        if ($user->getKeycloakId() === null) {
            $user->setInvitationToken(md5(uniqid()));
            $user->setCreatedAt(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();
            $this->mailerService->sendEmail(
                'SKIB Registrierung',
                '',
                $user->getEmail(),
                $this->translator->trans('Sie wurden als Mitarbeiter zu SKIB-Unsere Schulkindbetreuung hinzugefügt'),
                $this->environment->render('email/invitationEmail.html.twig', array('user' => $user, 'url' => $this->urlGenerator->generate('invitation_accept', array('token' => $user->getInvitationToken()),UrlGeneratorInterface::ABSOLUTE_URL), 'stadt' => $user->getStadt())),
                $creator->getEmail()
            );
        }

    }

    public function acceptInvitation($token, User $tempUser): ?User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('invitationToken' => $token));
        if (!$user) {
            return null;
        }
        $user->setInvitationToken(null);
        $user->setKeycloakId($tempUser->getKeycloakId());
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}