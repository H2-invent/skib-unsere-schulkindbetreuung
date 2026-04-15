<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ParentSickPortalAccess;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ParentSickPortalService
{
    public const SESSION_KEY_ACCESS = 'parent_sick_access_id';
    private const string VALIDITY_TIME = '24 hours';

    public function __construct(
        private UriSigner $uriSigner,
        private MailerService $mailerService,
        private Environment $twig,
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createAndSendAccessLink(ParentSickPortalAccess $access, Stadt $stadt): void
    {
        $access->setStadt($stadt);
        $this->addSignedUri($access);
        $this->sendEmail($access);

        $this->entityManager->persist($access);
        $this->entityManager->flush();
    }

    public function isLinkValid(ParentSickPortalAccess $access, Request $request): bool
    {
        if (!$this->uriSigner->checkRequest($request)) {
            return false;
        }

        $creationPlusInterval = $access->getCreatedAt()?->add(\DateInterval::createFromDateString(self::VALIDITY_TIME));

        return $creationPlusInterval instanceof \DateTimeImmutable && $creationPlusInterval >= new \DateTimeImmutable();
    }

    private function addSignedUri(ParentSickPortalAccess $access): void
    {
        $uri = $this->router->generate(
            'parent_sick_start',
            ['token' => $access->getToken()?->toRfc4122()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $access->setUri($this->uriSigner->sign($uri));
    }

    private function sendEmail(ParentSickPortalAccess $access): void
    {
        $mailContent = $this->twig->render('email/parent_sick_link.html.twig', [
            'uri' => $access->getUri(),
            'stadt' => $access->getStadt(),
        ]);

        $this->mailerService->sendEmail(
            $access->getStadt()?->getName() ?? 'Schulkindbetreuung',
            'noreply@unsere-schulkindbetreuung.de',
            $access->getEmail(),
            'Ihr Zugang zum Elterndashboard',
            $mailContent,
            'noreply@unsere-schulkindbetreuung.de'
        );
    }
}
