<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\Mailer\Bridge\Mailgun\Http\MailgunTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;

class MailerService
{


    private $mailgun;
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, MailerInterface $mailerInterface)
    {


        $this->mailgun =$mailerInterface;
       // $this->mailgun = new Mailer(new MailgunTransport('7a751a2f220d604c08e2e019224cfbe5-816b23ef-e28e0bcb', 'mail.h2-invent.com','eu'));
    }

    public function sendEmail($sender, $from, $to, $betreff,$content,$attachment = array())
    {


        $senderObj = new NamedAddress($from, $sender);
        $email = (new Email())
            ->from($senderObj)
            ->to($to)
            ->subject($betreff)
            ->html($content);
        foreach ($attachment as $data){
            $email->attach($data['body'],$data['filename'],$data['type']);
        };
       
        $this->mailgun->send($email);

/*
  $fromSender = array($from=>$sender);
        $message = (new \Swift_Message($betreff))
            ->setFrom($fromSender)

            ->setTo($to)
            ->setBody(
               $content,
                'text/html'
            );
        foreach ($attachment as $data){
            $message->attach((new \Swift_Attachment())
              ->setFilename($data['filename'])
              ->setContentType($data['type'])
              ->setBody($data['body']));

        }

        return   $this->mailer->send($message);
*/
    }
}
