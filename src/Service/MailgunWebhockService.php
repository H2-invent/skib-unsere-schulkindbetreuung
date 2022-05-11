<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\EmailResponse;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailgunWebhockService
{
    private $em;
    private $mailer;
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag, EntityManagerInterface $entityManager, MailerService  $mailer)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    public function saveFailure($parametersAsArray)
    {
        if($this->checkHash($parametersAsArray['signature']['signature'],$parametersAsArray['signature']['timestamp'].$parametersAsArray['signature']['token'])) {
            $emailResult = new EmailResponse();
            $stammdaten = $this->em->getRepository(Stammdaten::class)->findOneBy(array('email' => $parametersAsArray['event-data']['recipient']));
            $emailResult->setCreatedAt(new  \DateTime())
                ->setStammdaten($stammdaten)
                ->setStatus($parametersAsArray['event-data']['event'] == 'failed' ? true : false)
                ->setEvent($parametersAsArray['event-data']['event'])
                ->setSeverity($parametersAsArray['event-data']['severity'])
                ->setAllert($parametersAsArray['event-data']['log-level'] == 'error' ? true : false)
                ->setMessage($parametersAsArray['event-data']['delivery-status']['message'])
                ->setDescription($parametersAsArray['event-data']['delivery-status']['description'])
                ->setWarning($parametersAsArray['event-data']['log-level'] == 'warn' ? true : false)
                ->setPayload(json_encode($parametersAsArray))
                ->setMessageId($parametersAsArray['event-data']['message']['headers']['message-id'])
                ->setReciever($parametersAsArray['event-data']['recipient']);

            $this->em->persist($emailResult);
            $this->em->flush();

            $this->sendMail(
                $this->parameterBag->get('alarmEmail'),
                'Zustellung der Email fehlgeschlagen: ' . $emailResult->getSeverity(),
                'Message: ' . $emailResult->getDescription() . PHP_EOL .
                'Description: ' . $emailResult->getMessage() . PHP_EOL .
                'Reciepent: ' . $emailResult->getReciever() . PHP_EOL .
                'Complete: ' . print_r(json_decode($emailResult->getPayload()), true)
                ,
                'alarm@unsere-schulkindbetreuung.de'
            );
            return true;
        }
        return false;
    }

    public function saveSuccess($parametersAsArray)
    {
        if($this->checkHash($parametersAsArray['signature']['signature'],$parametersAsArray['signature']['timestamp'].$parametersAsArray['signature']['token'])) {
            $emailResult = new EmailResponse();
            $stammdaten = $this->em->getRepository(Stammdaten::class)->findOneBy(array('email' => $parametersAsArray['event-data']['recipient']));
            $emailResult->setCreatedAt(new  \DateTime())
                ->setStammdaten($stammdaten)
                ->setStatus($parametersAsArray['event-data']['event'] == 'failed' ? true : false)
                ->setEvent($parametersAsArray['event-data']['event'])
                ->setSeverity($parametersAsArray['event-data']['log-level'])
                ->setAllert($parametersAsArray['event-data']['log-level'] == 'error' ? true : false)
                ->setWarning($parametersAsArray['event-data']['log-level'] == 'warn' ? true : false)
                ->setPayload(json_encode($parametersAsArray))
                ->setMessageId($parametersAsArray['event-data']['message']['headers']['message-id'])
                ->setReciever($parametersAsArray['event-data']['recipient']);

            $this->em->persist($emailResult);
            $this->em->flush();
            return true;
        }
        return false;

    }

    function sendMail($to, $betreff, $text, $sender)
    {

        $this->mailer->sendEmail($sender,'alarm@unsere-schulkindbetreuung.de', $to,$betreff,$text );

    }

    public function checkHash($hash,$data){
        $newhash = hash_hmac('sha256' , $data , $this->parameterBag->get('mailgunApiKEy'));
        return $newhash == $hash;
    }
}
