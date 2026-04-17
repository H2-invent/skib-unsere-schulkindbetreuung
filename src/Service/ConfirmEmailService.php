<?php

namespace App\Service;

use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

// <- Add this

class ConfirmEmailService
{
    public function __construct(
        private FilesystemOperator $internFileSystem,
        private ParameterBagInterface $parameterbag,
        private MailerService $mailer,
        private Environment $twig,
        private FormFactoryInterface $formBuilder,
        private RouterInterface $router,
        private TranslatorInterface $translator,
        Security $security,
        private EntityManagerInterface $em,
    ) {
        $this->user = $security;
    }

    public function confirm(Stammdaten $stammdaten, Stadt $stadt, $redirect, Request $request)
    {
        if ($stammdaten->getEmailConfirmed()) {
            return null;
        }

        if ($stammdaten->getConfirmationCode() === null) {
            $stammdaten->setConfirmationCode(substr(str_shuffle(md5(microtime())), 0, 6));
            $this->em->persist($stammdaten);
            $this->em->flush();
        }

        $formData = ['confirmationCode' => '',
            'redirectUrl' => $redirect];
        $form = $this->formBuilder->create(ConfirmType::class, $formData);

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

            return new RedirectResponse($this->router->generate('workflow_confirm_Email', ['stadt' => $stadt->getId(), 'uid' => $stammdaten->getUid(), 'redirect' => $formData['redirectUrl'], 'snack' => $this->translator->trans('Bestätigungscode fehlerhaft')]));
        }
        $mailBetreff = $this->translator->trans('Bestätigung der E-Mail-Adresse');
        $mailContent = $this->twig->render('email/bestaetigungscode.html.twig', ['eltern' => $stammdaten, 'stadt' => $stadt]);
        $attachment = [];

        foreach ($stadt->getEmailDokumenteConfirm() as $att) {
            $attachment[] = [
                'body' => $this->internFileSystem->read($att->getFileName()),
                'filename' => $att->getOriginalName(),
                'type' => $att->getType(),
            ];
        }
        if ($stammdaten->getConfirmEmailSend() === false) {
            $this->mailer->sendEmail(
                'Unsere Schulkindbetreuung',
                $this->parameterbag->get('confirmEmailSender'),
                $stammdaten->getEmail(),
                $mailBetreff,
                $mailContent,
                $stadt->getEmail(),
                $attachment
            );
            $stammdaten->setConfirmEmailSend(true);
            $stammdaten->setResendEmail(md5(uniqid()));
            $this->em->persist($stammdaten);
            $this->em->flush();
        }

        $text = $this->translator->trans('Wir haben Ihnen einen Bestätigungscode an Ihre E-Mail-Adresse gesandt. Bitte geben Sie diesen Code aus der E-Mail hier ein. Dies ist notwendig um die Daten Ihrer Kinder bestmöglich zu schützen. Dies kann einige Minuten dauern. Bitte sehen Sie auch in Ihrem Spamordner nach.');

        return $this->twig->render('workflow/formConfirmation.html.twig', ['form' => $form->createView(), 'titel' => $mailBetreff . ':<br> ' . $stammdaten->getEmail(), 'text' => $text, 'stadt' => $stadt, 'stammdaten' => $stammdaten, 'redirect' => $request->get('redirect')]);
    }
}
