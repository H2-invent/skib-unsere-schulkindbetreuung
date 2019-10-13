<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\Templating\EngineInterface;

class MailerService
{
    private $mailer;
    private $templating;
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer =  $mailer;
        $this->templating = $templating;
    }

    public function sendEmail( $from, $to, $betreff,$content,$attachment = array())
    {


        $message = (new \Swift_Message($betreff))
            ->setFrom($from)
            ->setTo($to)

            ->setBody(
               $content,
                'text/html'
            );
        foreach ($attachment as $data){
            $message->attach($data);

        }

        return   $this->mailer->send($message);
    }
}