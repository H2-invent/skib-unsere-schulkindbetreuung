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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KontingentController extends AbstractController
{
    private KontingentAcceptService $acceptService;
    private LoggerInterface $logger;
    public function __construct(KontingentAcceptService $kontingentAcceptService, LoggerInterface $logger, private ManagerRegistry $managerRegistry)
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
