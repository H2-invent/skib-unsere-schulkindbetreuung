<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Log;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

// <- Add this

class ChildDeleteService
{
    public function __construct(
        private FilesystemOperator $internFileSystem,
        private LoggerInterface $logger,
        private ParameterBagInterface $parameterBag,
        private WorkflowAbschluss $abschluss,
        private MailerService $mailer,
        private Environment $templating,
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
    ) {
    }

    public function deleteChild(Kind $kind, User $user)
    {
        try {
            $childHist = $this->em->getRepository(Kind::class)->findHistoryOfThisChild($kind);
            foreach ($childHist as $data) {
                $data->setStartDate(null);
                $this->em->persist($data);
            }
            $this->em->flush();

            $message = 'child Deleted: Tracing' . $kind->getTracing() .
                'Name: ' . $kind->getVorname() . ' ' . $kind->getNachname() . '; ' .
                'fos_user_id: ' . $user->getId() . '; ';
            $log = new Log();
            $log->setUser($user->getEmail());
            $log->setDate(new \DateTime());
            $log->setMessage($message);
            $this->em->persist($log);
            $this->em->flush();
            if ($this->parameterBag->get('noEmailOnDelete') == 0) {
                $this->sendEmail($kind->getEltern(), $kind, $kind->getSchule()->getOrganisation());
            }

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function sendEmail(Stammdaten $stammdaten, Kind $kind, Organisation $organisation)
    {
        $mailBetreff = $this->translator->trans('Abmeldung der Schulkindbetreuung für ') . $kind->getVorname() . ' ' . $kind->getNachname();
        $mailContent = $this->templating->render('email/abmeldebestatigung.html.twig', ['eltern' => $stammdaten, 'kind' => $kind, 'org' => $organisation, 'stadt' => $organisation->getStadt()]);
        $attachment = [];
        foreach ($organisation->getStadt()->getEmailDokumenteSchulkindbetreuungAbmeldung() as $att) {
            $attachment[] = [
                'body' => $this->internFileSystem->read($att->getFileName()),
                'filename' => $att->getOriginalName(),
                'type' => $att->getType(),
            ];
        }
        $this->mailer->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $stammdaten->getEmail(),
            $mailBetreff,
            $mailContent,
            $kind->getSchule()->getOrganisation()->getEmail(),
            $attachment
        );
        foreach ($stammdaten->getPersonenberechtigters() as $data) {
            $this->mailer->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $data->getEmail(),
                $mailBetreff,
                $mailContent,
                $kind->getSchule()->getOrganisation()->getEmail(),
                $attachment
            );
        }
    }
}
