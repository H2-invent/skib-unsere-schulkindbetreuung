<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;

use App\Entity\Stammdaten;
use App\Entity\User;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


// <- Add this

class ChildDeleteService
{
    private $em;
    private $translator;
    private $templating;
    private $mailer;
    private $abschluss;
    private  $parameterBag;
    private $logger;
    public function __construct(LoggerInterface $logger, ParameterBagInterface $parameterBag, WorkflowAbschluss $workflowAbschluss, MailerService $mailer, Environment $environment, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->templating = $environment;
        $this->mailer = $mailer;
        $this->abschluss = $workflowAbschluss;
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
    }

    public function deleteChild(Kind $kind,User $user)
    {
        try {
            $parents = $kind->getEltern();
            $parentsNew = $this->em->getRepository(Stammdaten::class)->findOneBy(array('fin'=>false,'saved'=>false,'tracing'=>$parents->getTracing()));
            $kinds = $parentsNew->getKinds();
            $this->abschluss->abschluss($parentsNew,$kinds,$kind->getSchule()->getStadt());
            $kindAct = $this->em->getRepository(Kind::class)->findOneBy(array('saved'=>true,'fin'=>true,'tracing'=>$kind->getTracing()));
            $this->em->remove($kindAct);
            $kindClone = $this->em->getRepository(Kind::class)->findOneBy(array('saved'=>false,'fin'=>false,'tracing'=>$kind->getTracing()));
            $this->em->remove($kindClone);
            $parentsNew->setSecCode($parents->getSecCode());
            $this->em->persist($parentsNew);
            $this->em->flush();
            $this->logger->log(1,'DELETE CHILD '.$kind->getId());
            $this->logger->log(1,'DELETE TRACING ID '.$kind->getTracing());
            $this->logger->log(1,'DELETED FROM '.$user->getVorname() .' '.$user->getNachname());
            if($this->parameterBag->get('noEmailOnDelete') == 0){
                $this->sendEmail($kind->getEltern(), $kind, $kind->getSchule()->getOrganisation());
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }

    }

    public function sendEmail(Stammdaten $stammdaten, Kind $kind, Organisation $organisation)
    {
        $mailBetreff = $this->translator->trans('Abmeldung der Schulkindbetreuung für ') . $kind->getVorname() . ' ' . $kind->getNachname();
        $mailContent = $this->templating->render('email/abmeldebestatigung.html.twig', array('eltern' => $stammdaten, 'kind' => $kind, 'org' => $organisation, 'stadt' => $organisation->getStadt()));
        $this->mailer->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $stammdaten->getEmail(),
            $mailBetreff,
            $mailContent,
            $kind->getSchule()->getOrganisation()->getEmail()
        );

    }
}
