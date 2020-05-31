<?php

namespace App\Service;

use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


// <- Add this

class ConfirmEmailService
{


    private $em;
    private $translator;
    private $router;
    private $formBuilder;
    private $twig;
    private $mailer;
    private $parameterbag;
   public function __construct(ParameterBagInterface $parameterBag, MailerService $mailerService,Environment $twig, FormFactoryInterface $formBuilder,RouterInterface $router,TranslatorInterface $translator,Security $security,EntityManagerInterface $entityManager)
   {
       $this->em = $entityManager;
       $this->user = $security;
       $this->translator = $translator;
       $this->router = $router;
       $this->formBuilder = $formBuilder;
       $this->twig = $twig;
       $this->mailer= $mailerService;
       $this->parameterbag = $parameterBag;
   }

    public
    function confirm(Stammdaten $stammdaten,Stadt $stadt, $redirect,Request $request )
    {
        if ($stammdaten->getEmailConfirmed()) {
            return null;
        } else {

            if ($stammdaten->getConfirmationCode() === null) {
                $stammdaten->setConfirmationCode(substr(str_shuffle(MD5(microtime())), 0, 6));
                $this->em->persist($stammdaten);
                $this->em->flush();
            }



            $formData = array('confirmationCode' => '',
                'redirectUrl' => $redirect);
            $form = $this->formBuilder->create(ConfirmType::class,$formData);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                if ($formData['confirmationCode'] == $stammdaten->getConfirmationCode()) {
                    $stammdaten->setEmailConfirmed(true);
                    $stammdaten->setIpAdresse($request->getClientIp());
                    $stammdaten->setConfirmDate(new \DateTime());
                    $this->em->persist($stammdaten);
                    $this->em->flush();
                    return null;
                }
                return new RedirectResponse($this->router->generate('workflow_confirm_Email', array('stadt' => $stadt->getId(), 'uid' => $stammdaten->getUid(), 'redirect' => $formData['redirectUrl'], 'snack' => $this->translator->trans('Bestätigungscode fehlerhaft'))));
            }
            $mailBetreff = $this->translator->trans('Bestätigung der E-Mail-Adresse');
            $mailContent = $this->twig->render('email/bestaetigungscode.html.twig', array('eltern' => $stammdaten, 'stadt'=>$stadt));
            if ($stammdaten->getConfirmEmailSend() === false) {
                $this->mailer->sendEmail('Unsere Schulkindbetreuung', $this->parameterbag->get('confirmEmailSender'), $stammdaten->getEmail(), $mailBetreff, $mailContent);
                $stammdaten->setConfirmEmailSend(true);
                $stammdaten->setResendEmail(md5(uniqid()));
                $this->em->persist($stammdaten);
                $this->em->flush();
            }

            $text = $this->translator->trans('Wir haben Ihnen einen Bestätigungscode an Ihre E-Mail-Adresse gesandt. Bitte geben Sie diesen Code aus der E-Mail hier ein. Dies ist notwendig um die Daten Ihrer Kinder bestmöglich zu schützen. Dies kann einige Minuten dauern. Bitte sehen Sie auch in Ihrem Spamordner nach.');

            return $this->twig->render('workflow/formConfirmation.html.twig', array('form' => $form->createView(), 'titel' => $mailBetreff.':<br> '.$stammdaten->getEmail(), 'text' => $text, 'stadt' => $stadt, 'stammdaten' => $stammdaten, 'redirect' => $request->get('redirect')));


        }
    }

}
