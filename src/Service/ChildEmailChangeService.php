<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Log;
use App\Entity\Stammdaten;
use App\Entity\User;
use App\Form\Type\ChildChangeEmailType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

// <- Add this

class ChildEmailChangeService
{
    public function __construct(
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
        private AnmeldeEmailService $email,
        private FormFactoryInterface $form,
        private ElternService $elternService,
    ) {
    }

    public function form(Kind $kind)
    {
        $input = ['email' => $this->elternService->getLatestElternFromChild($kind)->getEmail(), 'emailDoubleInput' => ''];

        $form = $this->form->create(ChildChangeEmailType::class, $input);

        return $form;
    }

    public function changeEmail(Kind $kind, $input, User $user)
    {
        $elternOne = $this->elternService->getLatestElternFromChild($kind);
        $elternAll = $this->em->getRepository(Stammdaten::class)->findBy(['tracing' => $elternOne->getTracing()]);

        $message = 'Email addresse changed from ' . $elternOne->getEmail() . ' to ' . $input['email'] . '; ' .
            'stamm_id: ' . $elternOne->getId() . '; ' .
            'fos_user_id: ' . $user->getId() . '; ' .
            'Tracing: ' . $elternOne->getTracing();
        $log = new Log();
        $log->setUser($user->getEmail());
        $log->setDate(new \DateTime());
        $log->setMessage($message);
        $this->em->persist($log);
        foreach ($elternAll as $data) {
            $data->setEmail($input['email']);
            $data->setEmailDoubleInput($input['emailDoubleInput']);
            $this->em->persist($data);
        }
        $this->em->flush();

        foreach ($elternOne->getKinds() as $data2) {
            $this->email->sendEmail($data2, $elternOne, $data2->getSchule()->getStadt(), $this->translator->trans('Ihre E-Mail Adresse wurde von einem Mitarbeiter der betreuenden Organisation geändert. Aus diesem Grund senden wir Ihnen die Buchungsbstätigung dieses Kindes nochmals zu:', [], $elternOne->getLanguage()));
            $this->email->setBetreff($this->translator->trans('Ihre E-Mail Adresse wurde von einem Mitarbeiter der betreuenden Oranisation geändert.', [], $elternOne->getLanguage()));
            $this->email->send($data2, $this->elternService->getLatestElternFromChild($data2));
        }

        return true;
    }
}
