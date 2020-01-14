<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Mailer\Bridge\Mailgun\Http\MailgunTransport;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;
use function MongoDB\BSON\fromJSON;

class MailerService
{


    private $mailgun;
    private $smtp;
    private $swift;
    public function __construct(MailerInterface $mailerInterface, TransportInterface $smtp,\Swift_Mailer $swift_Mailer)
    {
        $this->smtp = $smtp;
        $this->mailgun =$mailerInterface;
        $this->swift = $swift_Mailer;
    }

    public function sendEmail($sender, $from, $to, $betreff,$content,$attachment = array())
    {

        $env = new Dotenv();
        $env->load(__DIR__.'/../../.env', __DIR__.'/../../.env.local');
        $mailprovider = $_ENV['MAILPROVIDER'];
        if($mailprovider == 'MAILGUN'){
            $this->sendViaMailgun($sender,$from,$to,$betreff,$content,$attachment);
        }elseif ($mailprovider=='SWIFTMAILER'){
            $this->sendViaSwiftMailer($sender,$from,$to,$betreff,$content,$attachment);
        }

    }

    private function sendViaSwiftMailer($sender, $from, $to, $betreff,$content,$attachment = array()){
        $message = (new \Swift_Message($betreff))
            ->setFrom(array('noreply@unsere-schulkindbetreuung.de'=>$sender))
            ->setTo($to)
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
    private function sendViaMailgun($sender, $from, $to, $betreff,$content,$attachment = array()){
        $email = (new Email())
            ->from(new Address($from, $sender))
            ->to($to)
            ->subject($betreff)
            ->html($content);
        foreach ($attachment as $data){
            $email->attach($data['body'],$data['filename'],$data['type']);
        };

        $this->mailgun->send($email);
    }
}
