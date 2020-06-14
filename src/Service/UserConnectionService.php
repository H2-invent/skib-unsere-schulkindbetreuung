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


    private $em;
    private $router;
    private $twig;
    private $mailer;

    public function __construct(MailerService $mailerService, Environment $twig, RouterInterface $router, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->router = $router;
        $this->twig = $twig;
        $this->mailer = $mailerService;

    }

    public function generateConfirmationToken(?User $user)
    {
        try {
            $user->setConfirmationTokenApp(substr(md5(uniqid()), 0, 6));
            $user->setAppDetectionToken(md5(uniqid()));

            $this->em->persist($user);
            $this->em->flush();
            $stadt = $user->getStadt();
            $this->mailer->sendEmail('Unsere Schulkindbetreuung', 'test@local.com', $user->getEmail(), 'Bestätigungscode für die SKIBin App', $this->twig->render('email/appConfirmationCode.html.twig', array('user' => $user, 'stadt' => $stadt)));
            return array(
                'error' => false,
                'token' => $user->getAppDetectionToken(),
                'url' => $this->router->generate('connect_communication_token'));
        } catch (\Exception $e) {
            return array('error' => true);
        }
    }

    public function generateCommunicationToken(?User $user)
    {
        try {
            if ($user) {
                $user->setAppCommunicationToken(md5(uniqid()));

                $this->em->persist($user);
                $this->em->flush();
                return array(
                    'error' => false,
                    'token' => $user->getAppCommunicationToken(),
                    'url' =>
                        str_replace('http','https',
                            str_replace('https','http',
                                $this->router->generate('connect_user_information',[], UrlGenerator::ABSOLUTE_URL)
                            )
                        ),
                    'user'=>$this->userInfo($user)
                );
            } else {
                return array('error' => true);
            }

        } catch (\Exception $e) {
            return array('error' => true);

        }

    }

    public function userInfo(?User $user)
    {
        try {
            if ($user) {
                $res = array();
                $res['info']= array(
                    'firstName'=>$user->getVorname(),
                    'lastName'=>$user->getNachname(),
                    'email'=>$user->getEmail(),
                    'organisation'=>$user->getOrganisation()->getName());
                $res['url']= array(
                    array('name'=>'Angemeldete Kinder','url'=>'url der angemeldeten Kinder'),
                    array('name'=>'Eingecheckte Kinder','url'=>'url der eingecheckten kinder')
                );
                return $res;
            } else {
                return array('error' => true);
            }
        } catch (\Exception $e) {
            return array('errosr' => true);
        }
    }
}
