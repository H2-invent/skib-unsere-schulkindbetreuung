<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Service\KontingentAcceptService;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KontingentController extends AbstractController
{
    private KontingentAcceptService $acceptService;
    private LoggerInterface $logger;
    public function __construct(
        KontingentAcceptService $kontingentAcceptService,
        LoggerInterface $logger,
        private ManagerRegistry $managerRegistry,
    private LoerrachWorkflowController $loerrachWorkflowController)
    {
        $this->acceptService = $kontingentAcceptService;
        $this->logger = $logger;
    }

    /**
     * @Route("/org_accept/accept_all", name="kontingent_accept_all_kids",methods={"GET"})
     */
    public function acceptAll(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

            $this->acceptService->acceptAllkindOfZeitblock($block);

            return new JsonResponse(array('error' => 0, 'snack' => $translator->trans('Erfolgreich gespeichert')));

    }
    /**
     * @Route("/org_accept/resend_confirmation/{kindId}", name="kontingent_resend_confirmation",methods={"GET"})
     */
    public function resendCOnfirmation(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, $kindId)
    {
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($kindId);
        try {
            if ($kind && $kind->getSchule()->getOrganisation() === $this->getUser()->getOrganisation()){
                $this->acceptService->beworbenCheck($kind);
            }else{
                $this->addFlash('danger',$translator->trans('Kind nicht vorhanden.'));
            }
            $this->addFlash('success',$translator->trans('Erfolgreich gesendet.'));
        }catch (\Exception $exception){
            $this->addFlash('danger',$translator->trans('Bestätigung konnte nicht gesendet werden.'));
        }

        return  new RedirectResponse($request->headers->get('referer'));


    }

    /**
     * @Route("/org_accept/show_kids", name="kontingent_show_kids",methods={"GET"})
     */
    public function schowAllKids(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $fictiveDate = $request->get('fictiveDate')?new \DateTime($request->get('fictiveDate')):(new \DateTime())->modify('first day of next month');
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $kind = $this->managerRegistry->getRepository(Kind::class)->findBeworbenByZeitblock($block);

        return $this->render('kontingent/child.html.twig', array('fictiveDate'=>$fictiveDate,'text' => $translator->trans('Akzeptieren oder lehnen Sie ein Kind für diesen Block ab'), 'block' => $block, 'kinder' => $kind));

    }

    /**
     * @Route("/org_accept/download_kids", name="kontingent_download_kids",methods={"GET"})
     */
    public function downloadAllKids(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {

        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $kind = $this->managerRegistry->getRepository(Kind::class)->findBeworbenByZeitblock($block);

        $csvData = [];
        $csvData[] = [
            'Vorname',
            'Nachname',
            'Geburtsdatum (TT.MM.JJJJ)',
            'Jahrgang',
            'Bemerkung',
            'Eltern',
            'Berufliche Situation',
            'Alleinerziehend'
        ];

        foreach ($kind as $child) {
            /**
             * @var Kind $child
             */
            $workflow = $this->loerrachWorkflowController;
            $beruflicheSituation = array_flip($workflow->beruflicheSituation)[$child->getEltern()->getBeruflicheSituation()] ?? 'Keine Angabe';

            $row = [
                $child->getVorname(),
                $child->getNachname(),
                $child->getGeburtstag()->format('d.m.Y'),
                $child->getKlasseString(),
                $child->getBemerkung(),
                $child->getEltern()->getVorname().' '.$child->getEltern()->getName(),
                $beruflicheSituation,
                $child->getEltern()->getAlleinerziehend()?'Ja':'Nein'
            ];

            $csvData[] = $row;
        }

        $fileName = 'Angemeldete Kinder im Block: '.$block->getId().'.csv';

        $response = new Response();
        $response->setContent($this->arrayToCsv($csvData));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . $fileName . '"');

        return $response;
    }

    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        return stream_get_contents($output);
    }
    /**
     * @Route("/org_accept/accept/kid", name="kontingent_accept_kid",methods={"GET"})
     */
    public function acceptKid(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));
        try {
            $this->acceptService->acceptKind($block, $kind);
            return new JsonResponse(array('snack' => $translator->trans('Erfolgreich gespeichert')));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse(array('snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
        }
    }

    /**
     * @Route("/org_accept/accept/kid/silent", name="kontingent_accept_kid_silent",methods={"GET"})
     */
    public function acceptKidSilent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));
        try {
            $this->acceptService->acceptKind($block, $kind,true);
            return new JsonResponse(array('snack' => $translator->trans('Erfolgreich gespeichert')));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse(array('snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
        }
    }
    /**
     * @Route("/org_accept/accept/kid/AllBlocks", name="kontingent_accept_kid_AllBlocks",methods={"GET"})
     */
    public function acceptKidAllBlocks(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));
        try {
            $this->acceptService->acceptAllZeitblockOfSpecificKind($kind);
            return new JsonResponse(array('snack' => $translator->trans('Erfolgreich gespeichert')));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse(array('snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
        }
    }
    /**
     * @Route("/org_accept/remove/kid", name="kontingent_remove_kid",methods={"GET"})
     */
    public function removeKid(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($this->getUser()->getOrganisation() != $block->getSchule()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));
        try {
            if (in_array($block, $kind->getBeworben()->toArray())) {

                $logger->info('Remove Beworbenes Kind from Block:' . json_encode($kind));

                $kind->removeBeworben($block);
                $em = $this->managerRegistry->getManager();
                $em->persist($kind);
                $em->flush();
                return new JsonResponse(array('snack' => $translator->trans('Erfolgreich gespeichert')));

            }
        } catch (\Exception $e) {
            $logger = $this->get('logger');
            $logger->err('Kind could not be removed from block: ' . json_encode($kind));
            return new JsonResponse(array('snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
        }
    }
}
