<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\LateRegistration;
use App\Repository\LateRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class LateRegistrationService
{
    private const VALIDITY_TIME = '3 days';
    private const SESSION_KEY_LATE_REGISTRATION = 'late_registration_id';

    public function __construct(
        private UriSigner $uriSigner,
        private MailerService $mailerService,
        private Environment $twig,
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
        private LateRegistrationRepository $lateRegistrationRepository,
    )
    {
    }

    public function create(LateRegistration $lateRegistration): void
    {
        $this->addSignedUri($lateRegistration);
        $this->sendEmail($lateRegistration);
        $this->entityManager->persist($lateRegistration);
        $this->entityManager->flush();
    }

    public function start(LateRegistration $lateRegistration, Request $request): void
    {
        $session = $request->getSession();
        $session->set(SchuljahrService::SESSION_KEY_SCHULJAHR, $lateRegistration->getSchuljahr()->getId());
        $session->set(self::SESSION_KEY_LATE_REGISTRATION, $lateRegistration->getId());
    }

    public function finish(LateRegistration $lateRegistration, Request $request): void
    {
        $lateRegistration->setUsedAtValue();
        $this->entityManager->persist($lateRegistration);
        $this->entityManager->flush();

        $session = $request->getSession();
        $session->remove(self::SESSION_KEY_LATE_REGISTRATION);
        $session->remove(SchuljahrService::SESSION_KEY_SCHULJAHR);
    }

    public function isValid(LateRegistration $lateRegistration, Request $request): bool
    {
        if ($this->isExpired($lateRegistration)) {
            return false;
        }
        if ($this->alreadyUsed($lateRegistration)) {
            return false;
        }
        if (!$this->uriSigner->checkRequest($request)) {
            return false;
        }

        return true;
    }

    public function getStartedLateRegistration(Request $request): ?LateRegistration
    {
        $lateRegistrationId = $request->getSession()->get(self::SESSION_KEY_LATE_REGISTRATION, null);
        if ($lateRegistrationId === null) {
            return null;
        }

        return $this->lateRegistrationRepository->find($lateRegistrationId);
    }

    private function addSignedUri(LateRegistration $lateRegistration): void
    {
        $uri = $this->router->generate(
            'late_registration_start',
            ['token' => $lateRegistration->getToken()->toRfc4122()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $signedUri = $this->uriSigner->sign($uri);
        $lateRegistration->setUri($signedUri);
    }

    private function sendEmail(LateRegistration $lateRegistration): void
    {
        $mailContent = $this->twig->render('email/late_registration.html.twig', [
            'uri' => $lateRegistration->getUri(),
            'stadt' => $lateRegistration->getStadt(),
        ]);

        $this->mailerService->sendEmail(
            $lateRegistration->getStadt()->getName(),
            'noreply@unsere-schulkindbetreuung.de',
            $lateRegistration->getEmail(),
            'Einmallink zur Anmeldung',
            $mailContent,
            'noreply@unsere-schulkindbetreuung.de',
        );
    }

    private function isExpired(LateRegistration $lateRegistration): bool
    {
        $createdAt = $lateRegistration->getCreatedAt();
        $creationPlusInterval = $createdAt->add(\DateInterval::createFromDateString(self::VALIDITY_TIME));

        return $creationPlusInterval < new \DateTime();
    }

    private function alreadyUsed(LateRegistration $lateRegistration): bool
    {
        return $lateRegistration->getUsedAt() !== null;
    }
}
