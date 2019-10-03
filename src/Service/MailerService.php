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

    public function sendEmail($capital1, $body1, $capital2, $body2, $from, $to, $betreff)
    {


        $message = (new \Swift_Message($betreff))
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $this->templating->render(
                // templates/hello/email.txt.twig
                    'email/base.html.twig',
                    [
                        'capital1' => $capital1,
                        'body1'=>$body1,
                        'capital2'=>$capital2,
                        'body2'=>$body2
                    ]
                ),
                'text/html'
            );





        return   $this->mailer->send($message);
    }
}