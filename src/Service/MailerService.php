<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Bridge\Mailgun\Http\MailgunTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{


    private $mailgun;

    private $swift;
    private $parameter;
    public function __construct(ParameterBagInterface $parameterBag, MailerInterface $mailerInterface, \Swift_Mailer $swift_Mailer)
    {

        $this->mailgun =$mailerInterface;
        $this->swift = $swift_Mailer;

        $this->parameter = $parameterBag;

    }

    public function sendEmail($sender, $from, $to, $betreff,$content,$replyTo,$attachment = array())
    {
        $from = $this->parameter->get('confirmEmailSender');
        if($this->parameter->get('mailprovider') == 'MAILGUN'){
            $this->sendViaMailgun($sender,$from,$to,$betreff,$content,$replyTo,$attachment);
        }elseif ($this->parameter->get('mailprovider')=='SWIFTMAILER'){
            $this->sendViaSwiftMailer($sender,$to,$betreff,$content,$replyTo,$attachment);

        }

    }

    private function sendViaSwiftMailer($sender,  $to, $betreff,$content,$replyTo,$attachment = array()){
        $message = (new \Swift_Message($betreff))
            ->setFrom(array('noreply@unsere-schulkindbetreuung.de'=>$sender))
            ->setTo($to)
            ->setReplyTo($replyTo)
            ->setBody(

                $content
                ,'text/html'
            )

        ;
        foreach ($attachment as $data){
            $message->attach(new \Swift_Attachment($data['body'],$data['filename'],$data['type']));
        };
        $this->swift->send($message);
    }
    private function sendViaMailgun($sender, $from, $to, $betreff,$content,$replyTo,$attachment = array()){
        $email = (new Email())
            ->from(new Address($from, $sender))
            ->to($to)
            ->replyTo($replyTo)
            ->subject($betreff)
            ->html($content);
        foreach ($attachment as $data){
            $email->attach($data['body'],$data['filename'],$data['type']);
        };

        $this->mailgun->send($email);
    }
}
