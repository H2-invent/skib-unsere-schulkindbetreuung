<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

// <- Add this

class UserConnectionService
{
    public function __construct(
        private MailerService $mailer,
        private Environment $twig,
        private RouterInterface $router,
        private EntityManagerInterface $em,
    ) {
    }

    public function generateConfirmationToken(?User $user)
    {
        try {
            $user->setConfirmationTokenApp(substr(md5(uniqid()), 0, 6));
            $user->setAppDetectionToken(md5(uniqid()));

            $this->em->persist($user);
            $this->em->flush();
            $stadt = $user->getStadt();
            $this->mailer->sendEmail(
                'Unsere Schulkindbetreuung',
                'test@local.com',
                $user->getEmail(),
                'Bestätigungscode für die SKIBin App',
                $this->twig->render('email/appConfirmationCode.html.twig',
                    ['user' => $user, 'stadt' => $stadt]),
                $stadt->getEmail(),
                null
            );

            return [
                'type' => 'USER',
                'error' => false,
                'token' => $user->getAppDetectionToken(),
                'url' => str_replace('http', 'https',
                    str_replace('https', 'http', $this->router->generate('connect_communication_token', [], UrlGenerator::ABSOLUTE_URL)
                    )
                ),
            ];
        } catch (\Exception) {
            return ['error' => true];
        }
    }

    public function generateCommunicationToken(?User $user)
    {
        try {
            if ($user) {
                $user->setAppCommunicationToken(md5(uniqid()));

                $this->em->persist($user);
                $this->em->flush();

                return [
                    'error' => false,
                    'token' => $user->getAppCommunicationToken(),
                    'url' => str_replace('http', 'https',
                        str_replace('https', 'http',
                            $this->router->generate('connect_user_information', [], UrlGenerator::ABSOLUTE_URL)
                        )
                    ),
                    'user' => $this->userInfo($user),
                    'urlSave' => str_replace('http', 'https',
                        str_replace('https', 'http', $this->router->generate('connect_communication_save', [], UrlGenerator::ABSOLUTE_URL)
                        )
                    ),
                ];
            }

            return ['error' => true];
        } catch (\Exception) {
            return ['error' => true];
        }
    }

    public function userInfo(?User $user)
    {
        try {
            if ($user) {
                $res = [];
                $res['info'] = [
                    'firstName' => $user->getVorname(),
                    'lastName' => $user->getNachname(),
                    'email' => $user->getEmail(),
                    'organisation' => $user->getOrganisation()->getName(),
                    'urlCheckinKids' => str_replace('http', 'https',
                        str_replace('https', 'http', $this->router->generate('connect_user_checkinKids', [], UrlGenerator::ABSOLUTE_URL))),
                    'urlKinderListeHeute' => str_replace('http', 'https',
                        str_replace('https', 'http', $this->router->generate('connect_user_kidsDa', [], UrlGenerator::ABSOLUTE_URL)
                        ))]
                ;

                return $res;
            }

            return ['error' => true];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function saveSetting(?User $user)
    {
        try {
            if ($user) {
                $user->setAppSettingsSaved(true);
                $user->setConfirmationTokenApp(null);
                $user->setAppDetectionToken(null);
                $user->setAppToken(null);
                $this->em->persist($user);
                $this->em->flush();

                return ['error' => false];
            }

            return ['error' => true];
        } catch (\Exception) {
            return ['error' => true];
        }
    }

    public function kinderCheckedIn(User $user)
    {
    }
}
