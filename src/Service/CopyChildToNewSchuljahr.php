<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CopyChildToNewSchuljahr
{
    private ChildSearchService $childSearchService;
    private EntityManagerInterface $entityManager;
    private ElternService $elternService;
    private WorkflowAbschluss $workflowAbschluss;
    private MailerService $mailerService;
    private Environment $twig;
    private AnmeldeEmailService $anmeldeEmailService;
    private TranslatorInterface $translator;

    public function __construct(ChildSearchService $childSearchService, EntityManagerInterface $entityManager, ElternService $elternService, WorkflowAbschluss $workflowAbschluss, MailerService $mailerService, Environment $environment, AnmeldeEmailService $anmeldeEmailService, TranslatorInterface $translator)
    {
        $this->childSearchService = $childSearchService;
        $this->entityManager = $entityManager;
        $this->elternService = $elternService;
        $this->workflowAbschluss = $workflowAbschluss;
        $this->mailerService = $mailerService;
        $this->twig = $environment;
        $this->anmeldeEmailService = $anmeldeEmailService;
        $this->translator = $translator;
    }

    public function copyKinderToSchuljahr(Active $source, Active $target, \DateTime $stichtag, $matrix, $blockmatrix, Output $output,$sendEmails=false): ?Active
    {


        $kinder = $this->childSearchService->searchChild(array('schuljahr' => $source->getId()), null, false, null, $stichtag, null, $source->getStadt());
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
            $elternNeu->setUid(md5(uniqid()));
            $elternNeu->setSecCode(null);
            $elternNeu->setTracingOfLastYear($elternAlt->getTracing());
            foreach ($elternNeu->getKinds() as $data) {
                $elternNeu->removeKind($data);
            }


            foreach ($kinder as $kind) {
                if (isset($matrix[$kind->getKlasse()])) {
                    $neuesSchuljahr = $matrix[$kind->getKlasse()];
                    $blocks = $kind->getZeitblocks()->toArray();
                    $kindTmp = clone $kind;
                    $kindTmp->setKlasse($neuesSchuljahr);
                    $kindTmp->setStartDate($target->getVon());
                    $kindTmp->setTracing(md5(uniqid()));
                    $this->entityManager->persist($kindTmp);
                    foreach ($kindTmp->getZeitblocks() as $oldBlocks) {
                        $kindTmp->removeZeitblock($oldBlocks);
                    }
                    foreach ($blocks as $block) {
                        if (isset($blockmatrix[$block->getId()])) {
                            foreach ($blockmatrix[$block->getId()] as $bMat) {
                                $tmpBlock = $this->entityManager->getRepository(Zeitblock::class)->find($bMat);
                                if ($tmpBlock) {
                                    $kindTmp->addZeitblock($tmpBlock);
                                }
                            }
                        } else {
                            $tmpBlock = $this->entityManager->getRepository(Zeitblock::class)->findOneBy(array('active' => $target, 'cloneOf' => $block));
                            if ($tmpBlock) {
                                $kindTmp->addZeitblock($tmpBlock);
                            }
                        }
                    }
                    foreach ($kinderTmp->getBeworben() as $beworben) {
                        $kindTmp->removeBeworben($beworben);
                    }
                    $kindTmp->setEltern($elternNeu);
                    $elternNeu->addKind($kindTmp);
                    $this->entityManager->persist($kindTmp);

                } else {
                    if ($sendEmails){
                        $this->sendABmeldeEmail($elternAlt, $kind, $source);
                    }

                }
            }
            if (sizeof($elternNeu->getKinds()) > 0) {
                $elternNeu = $this->createPeripherie($elternAlt, $elternNeu);
                $this->entityManager->persist($elternNeu);
                $this->entityManager->flush();
                $this->workflowAbschluss->abschluss($elternNeu, $target->getStadt());
                foreach ($elternNeu->getKinds() as $data2) {
                    $this->entityManager->refresh($elternNeu);
                    $this->entityManager->refresh($data2);
                    if ($sendEmails){
                        $this->sendAnmedebestaetigung($data2, $elternNeu, $source->getStadt(), $this->translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihres Kindes:'));
                    }

                }
            }
        }

        $progressBar->finish();
        return $target;
    }
//{"0":"Grundschulförderklasse","1":"1. Klasse","2":"2. Klasse","3":"3. Klasse","4":"4. Klasse","5":"1. Klasse (A)","6":"1. Klasse (B)","7":"1. Klasse (C)","8":"2. Klasse (A)","9":"2. Klasse (B)","10":"2. Klasse (C)","11":"3. Klasse (A)","12":"3. Klasse (B)","13":"3. Klasse (C)","14":"4. Klasse (A)","15":"4. Klasse (B)","16":"4. Klasse (C)"}
//Schorndorf: {"0":1,"1":2,"2":3,"3":4,"5":8,"6":9,"7":10,"8":11,"9":12,"10":13,"11":14,"12":15,"13":16}
//Schorndorf: {"1213":[2631,2491],"1219":[2631,2491],"1222":[2636,2506],"1228":[2636,2506],"1231":[2641,2521],"1237":[2641,2521],"1240":[2646,2536],"1246":[2646,2536],"1249":[2651,2551],"1255":[2651,2551]}
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
            $this->twig->render('email/betreuungsEnde.html.twig', array('stammdaten' => $stammdaten, 'kind' => $kind, 'schuljahr' => $active, 'stadt' => $active->getStadt())),
            $kind->getSchule()->getOrganisation()->getEmail());

        foreach ($stammdaten->getPersonenberechtigters() as $data2) {

            $this->mailerService->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $data2->getEmail(),
                'Ende der Schulkindbetreuung nach diesem Schuljahr',
                $this->twig->render('email/betreuungsEnde.html.twig', array('stammdaten' => $stammdaten, 'kind' => $kind, 'schuljahr' => $active, 'stadt' => $active->getStadt())),
                $kind->getSchule()->getOrganisation()->getEmail());

        }
    }

    public function sendAnmedebestaetigung(Kind $kind, Stammdaten $stammdaten, Stadt $stadt, $text, $dontSendBeworben = false)
    {
       $emailMustSended =  $this->anmeldeEmailService->sendEmail($kind, $stammdaten, $stadt, $text, $dontSendBeworben);
       if ($emailMustSended === true){
           $this->anmeldeEmailService->send($kind, $stammdaten);
           return true;
       }
       return false;
    }


    public function fixChildInTwoYears(Kind $kind){
        $allChilds = $this->entityManager->getRepository(Kind::class)->findBy(array('tracing'=>$kind->getTracing()));
        $correctChild = null;
        foreach ($allChilds as $data){
            $schuljahrTmp = null;
            $snapIn = true;
            foreach ($data->getZeitblocks() as $block){
                if ($block->getActive() !== $schuljahrTmp){
                    if ($schuljahrTmp !== null){
                        $snapIn = false;
                    }
                    $schuljahrTmp = $block->getActive();
                }
            }
            if ($snapIn){
                $correctChild = $data;
            }
        }
        foreach ($allChilds as $data){
            if ($data !== $correctChild){
                foreach ($data->getZeitblocks() as $data2){
                    $data->removeZeitblock($data2);
                }

               $data->setStartDate($correctChild->getStartDate());
                foreach ($correctChild->getZeitblocks() as $data2){
                    $data->addZeitblock($data2);
                }
            }
            $this->entityManager->persist($data);
        }
       $this->entityManager->flush();
    }
}