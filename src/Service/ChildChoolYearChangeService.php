<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Log;

use App\Entity\Stammdaten;
use App\Entity\User;
use App\Form\Type\ChildChangeEmailType;
use App\Form\Type\ChildChangeShoolyearType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class ChildChoolYearChangeService
{
    private $em;
    private $translator;
    private $email;
    private $form;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, AnmeldeEmailService $anmeldeEmailService, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->email = $anmeldeEmailService;
        $this->form = $formBuilder;
    }

    public function form(Kind $kind)
    {
        $input = array('shoolyear' => $kind->getKlasse());

        $form = $this->form->create(ChildChangeShoolyearType::class, $input,array('kind'=>$kind));

        return $form;
    }


    public function changeSchoolyear(Kind $kind, $input, User $user)
    {

        $elternOne = $kind->getEltern();
        $kindernAll = $this->em->getRepository(Kind::class)->findBy(array('tracing' => $kind->getTracing()));

        $message = 'Schoolyear changed from ' . $kind->getKlasse() . ' to ' . $input['schoolyear'] . '; ' .
            'kind_id: ' . $kind->getId() . '; ' .
            'fos_user_id: ' . $user->getId() . '; ' .
            'Tracing: ' . $elternOne->getTracing();
        $log = new Log();
        $log->setUser($user->getEmail());
        $log->setDate(new \DateTime());
        $log->setMessage($message);
        $this->em->persist($log);

        foreach ($kindernAll as $data) {
            $data->setKlasse($input['schoolyear']);
            $this->em->persist($data);
        }
        $this->em->flush();

        $this->email->sendEmail($kind, $elternOne, $kind->getSchule()->getStadt(), $this->translator->trans('Das Schuljahr Ihres Kindes wurde von einem Mitarbeiter der betreuenden Organisation geändert. Aus diesem Grund senden wir Ihnen die Buchungsbstätigung dieses Kindes nochmals zu:', [], $elternOne->getLanguage()));
        $this->email->setBetreff($this->translator->trans('Das Schuljahr Ihres Kindes wurde von einem Mitarbeiter der betreuenden Oranisation geändert.', [], $elternOne->getLanguage()));
        $this->email->send($kind, $kind->getEltern());

        return true;

    }
}
