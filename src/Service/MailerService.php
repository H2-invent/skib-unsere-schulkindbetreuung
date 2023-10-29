<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{


    private $parameter;
    private $mailer;

    public function __construct(ParameterBagInterface $parameterBag, MailerInterface $mailer, private LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->parameter = $parameterBag;

    }

    public function sendEmail($sender, $from, $to, $betreff, $content, $replyTo, $attachment = array())
    {
        $from = $this->parameter->get('confirmEmailSender');
        if (!$to){
            return false;
        }
        $this->sendViaMailer($sender, $to, $betreff, $content, $replyTo, $attachment);
    }

    private function sendViaMailer($sender, $to, $betreff, $content, $replyTo, $attachment = array())
    {
        try {

            $message = (new Email())
                ->subject($betreff)
                ->from(new Address('noreply@unsere-schulkindbetreuung.de', $sender))
                ->to($to)
                ->html($content)
                ->replyTo($replyTo);

            foreach ($attachment as $data) {
                $message->attach($data['body'], $data['filename'], $data['type']);
            };

            $this->mailer->send($message);
        }catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
            $this->logger->error('to EMail',['email'=>$to]);
        }

    }
}
