<?php

namespace App\Service;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Payment;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


// <- Add this

class FerienAbschluss
{


    private $em;
    private $printer;
    private $ics;
    private $mailer;
    private $twig;
    public function __construct(Environment $environment, MailerService $mailerService, IcsService $icsService, EntityManagerInterface $entityManager, FerienPrintService $ferienPrintService)
    {
        $this->em = $entityManager;
        $this->printer = $ferienPrintService;
        $this->ics = $icsService;
        $this->mailer = $mailerService;
        $this->twig = $environment;
    }

    public
    function abschlussFin(Stammdaten $adresse)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }
        $adresse->setFin(true);
        $this->em->persist($adresse);
        foreach ($adresse->getKinds() as $data) {
            $data->setFin(true);
            $this->em->persist($data);
        }
        $this->em->persist($adresse);
        $this->em->flush();
    }

    public
    function abschlussSave(Stammdaten $adresse)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }

        $adresse->setSaved(true);
        $this->em->persist($adresse);
        foreach ($adresse->getKinds() as $data) {
            $data->setSaved(true);
            $this->em->persist($data);
        }
        $this->em->persist($adresse);
        $this->em->flush();
    }

    public
    function abschlussSendEmail(Stammdaten $adresse)
    {
        $kinder = $adresse->getKinds();
        $programm = array();
        foreach ($kinder as $data) {
            $programm = array_merge($programm,
                $data->getKindFerienblocksGebucht()->toArray());
        }


        $attachment = array();
        foreach ($programm as $data) {
            //pdf mit dem Tiket
            $ferienblock =  $data->getFerienblock();
            $kind =  $data->getKind();
            $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $ferienblock->translate()->getTitel();
            $pdf = $this->printer->printPdfTicket(
                $fileName . '.pdf',
                $data, 'S');
            $attachment[] = array('type' => 'application/pdf', 'filename' => $fileName . '.pdf', 'body' => $pdf);
            //ICS mit dem Termin
            $startDate = $ferienblock->getStartDate()->format('Ymd');
            $this->ics->add(
                array(
                    'location' => $ferienblock->getOrt(),
                    'description' => $data->getFerienblock()->translate()->getInfoText(),
                    'dtstart' => $startDate . 'T' . $ferienblock->getStartTime()->format('His'),
                    'dtend' => $startDate . 'T' . $ferienblock->getEndTime()->format('His'),
                    'summary' => $kind->getVorname().' '.$kind->getNachname().' '.$ferienblock->translate()->getTitel(),
                    'url' => '',
                    'rrule' => 'FREQ=WEEKLY;UNTIL=' . $ferienblock->getEndTime()->format('Ymd') . 'T000000'
                )
            );
        }
        $attachment[] = array('type' => 'text/calendar', 'filename' => 'Ferienprogramm.ics', 'body' => $this->ics->to_string());
        $this->mailer->sendEmail('SKIB Ferienprogramm',
            'info@h2-invent.com',
            $adresse->getEmail(),
            'Tickets zu dem gebuchten Ferienprogramm',
            $this->twig->render('email/anmeldebestatigungFerien.html.twig',array('stammdaten'=>$adresse)),
            $attachment);
        return 0;
    }

}
