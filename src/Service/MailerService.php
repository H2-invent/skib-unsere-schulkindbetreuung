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
use function MongoDB\BSON\fromJSON;

class MailerService
{


    private $mailgun;
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, MailerInterface $mailerInterface)
    {


        $this->mailgun =$mailerInterface;
    }

    public function sendEmail($sender, $from, $to, $betreff,$content,$attachment = array())
    {



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
