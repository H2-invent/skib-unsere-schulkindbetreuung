<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Twig\Environment;

class CopyChildToNewSchuljahr
{
    private ChildSearchService $childSearchService;
    private EntityManagerInterface $entityManager;
    private ElternService $elternService;
    private WorkflowAbschluss $workflowAbschluss;
    private MailerService $mailerService;
    private Environment $twig;

    public function __construct(ChildSearchService $childSearchService, EntityManagerInterface $entityManager, ElternService $elternService, WorkflowAbschluss $workflowAbschluss, MailerService $mailerService, Environment $environment)
    {
        $this->childSearchService = $childSearchService;
        $this->entityManager = $entityManager;
        $this->elternService = $elternService;
        $this->workflowAbschluss = $workflowAbschluss;
        $this->mailerService = $mailerService;
        $this->twig = $environment;

    }

    public function copyKinderToSchuljahr(Active $source, Active $target, \DateTime $stichtag, $matrix, Output $output):?Active
    {
        $kinderTarget = $this->childSearchService->searchChild(array('schuljahr'=>$target->getId()), null, false, null, $target->getVon(), null, $source->getStadt());
        if (sizeof($kinderTarget)>0){
            return null;
        }
        $kinder = $this->childSearchService->searchChild(array('schuljahr'=>$source->getId()), null, false, null, $stichtag, null, $source->getStadt());
        $kinderTmp = array();
        foreach ($kinder as $data) {
            $eltern = $this->elternService->getElternForSpecificTimeAndKind($data, $stichtag);
            $kinderTmp[$eltern->getId()][] = $data;
        }
        $progressBar = new ProgressBar($output, sizeof($kinderTmp));
        foreach ($kinderTmp as $key => $kinder) {
            $progressBar->advance();
            $elternAlt = $this->entityManager->getRepository(Stammdaten::class)->find($key);
            $elternNeu = clone $elternAlt;
            $elternNeu->setTracing(md5(uniqid()));
            $elternNeu->setStartDate($target->getVon());
            $elternNeu->setSecCode(null);
            foreach ($elternNeu->getKinds() as $data) {
                $elternNeu->removeKind($data);
            }


            foreach ($kinder as $kind) {
                if (isset($matrix[$kind->getKlasse()])) {
                    $neuesSchuljahr = $matrix[$kind->getKlasse()];
                    $kindTmp = clone $kind;
                    $kindTmp->setKlasse($neuesSchuljahr);
                    $kindTmp->setStartDate($target->getVon());
                    $kindTmp->setTracing(md5(uniqid()));

                    $this->entityManager->persist($kindTmp);

                    foreach ($kind->getZeitblocks() as $block) {
                        $tmpBlock = $this->entityManager->getRepository(Zeitblock::class)->findOneBy(array('active' => $target, 'cloneOf' => $block));
                        if ($tmpBlock) {
                            $kindTmp->addZeitblock($tmpBlock);
                        }
                    }
                    $kindTmp->setEltern($elternNeu);
                    $elternNeu->addKind($kindTmp);
                    $this->entityManager->persist($kindTmp);

                } else {
                    $this->sendABmeldeEmail($elternAlt,$kind,$source);
                }
            }
            if (sizeof($elternNeu->getKinds()) > 0) {
                $elternNeu = $this->createPeripherie($elternAlt, $elternNeu);
                $this->entityManager->persist($elternNeu);
                $this->entityManager->flush();
                $this->workflowAbschluss->abschluss($elternNeu, $target->getStadt());
            }

        }
        $progressBar->finish();
        return $target;
    }
//{"0":"Grundschulförderklasse","1":"1. Klasse","2":"2. Klasse","3":"3. Klasse","4":"4. Klasse","5":"1. Klasse (A)","6":"1. Klasse (B)","7":"1. Klasse (C)","8":"2. Klasse (A)","9":"2. Klasse (B)","10":"2. Klasse (C)","11":"3. Klasse (A)","12":"3. Klasse (B)","13":"3. Klasse (C)","14":"4. Klasse (A)","15":"4. Klasse (B)","16":"4. Klasse (C)"}
//{"0":1,"1":2,"2":3,"3":4,"5":8,"6":9,"7":10,"8":11,"9":12,"10":13,"11":14,"12":15,"13":16}
    public function createPeripherie(Stammdaten $source, Stammdaten $target)
    {

        foreach ($source->getGeschwisters() as $data) {
            $geschwisterNeu = clone $data;
            $geschwisterNeu->setStammdaten($target);
            $this->entityManager->persist($geschwisterNeu);
            $target->addGeschwister($geschwisterNeu);
        }

        foreach ($source->getPersonenberechtigters() as $data) {
            $persNeu = clone $data;
            $persNeu->setStammdaten($target);
            $this->entityManager->persist($persNeu);
            $target->addPersonenberechtigter($persNeu);
        }

        foreach ($source->getKundennummerns() as $data) {
            $kdnNr = clone $data;
            $kdnNr->setStammdaten($target);
            $this->entityManager->persist($kdnNr);
            $target->addKundennummern($kdnNr);
        }

        return $target;

    }

    public function sendABmeldeEmail(Stammdaten $stammdaten, Kind $kind, Active $active)
    {


        $this->mailerService->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $stammdaten->getEmail(),
            'Ende der Schulkindbetreuung nach diesem Schuljahr',
            $this->twig->render('email/betreuungsEnde.html.twig', array('stammdaten' => $stammdaten, 'kind' => $kind, 'schuljahr' => $active)),
            $kind->getSchule()->getOrganisation()->getEmail());

        foreach ($stammdaten->getPersonenberechtigters() as $data2) {

            $this->mailerService->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $data2->getEmail(),
                'Ende der Schulkindbetreuung nach diesem Schuljahr',
                $this->twig->render('email/betreuungsEnde.html.twig', array('stammdaten' => $stammdaten, 'kind' => $kind, 'schuljahr' => $active)),
                $kind->getSchule()->getOrganisation()->getEmail());

        }
    }
}