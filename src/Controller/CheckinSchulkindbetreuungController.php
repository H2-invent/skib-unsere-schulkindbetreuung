<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use App\Service\CheckinSchulkindservice;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckinSchulkindbetreuungController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/checkin/schulkindbetreuung/{kindID}', name: 'checkin_schulkindbetreuung', methods: ['POST'])]
    public function index(Request $request, TranslatorInterface $translator, $kindID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $today = (new \DateTime());
        $org = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $kind = $this->managerRegistry->getRepository(Kind::class)->find($kindID);
        $result = $checkinSchulkindservice->checkin($kind, $today, $org);

        return new JsonResponse($result);
    }

    #[Route(path: '/connect/organisation/{orgID}', name: 'connect_Org', methods: ['GET'])]
    public function connectOrg(Request $request, TranslatorInterface $translator, $orgID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $org = $this->managerRegistry->getRepository(Organisation::class)->find($orgID);

        return new JsonResponse([
            'type' => 'ORGANISATION',
            'id' => $org->getId(),
            'name' => $org->getName(),
            'partner' => $org->getAnsprechpartner(),
            'url' => str_replace('http', 'https', str_replace('https', 'http', $this->generateUrl('getOrganisationfromId', ['orgID' => $org->getId()], UrlGeneratorInterface::ABSOLUTE_URL))),
        ]
        );
    }

    #[Route(path: '/get/organisation/{orgID}', name: 'getOrganisationfromId', methods: ['GET'])]
    public function getORg(Request $request, TranslatorInterface $translator, $orgID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $org = $this->managerRegistry->getRepository(Organisation::class)->find($orgID);
        $today = new \DateTime();
        $kinder = $checkinSchulkindservice->getAllKidsToday($org, $today, null);

        return new JsonResponse([
            'name' => $org->getName(),
            'partner' => $org->getAnsprechpartner(),
            'tel' => $org->getTelefon(),
            'image' => $this->renderView('checkin_schulkindbetreuung/image.html.twig', ['org' => $org]),
            'anwesend' => sizeof($kinder),
        ]
        );
    }

    #[Route(path: '/org_checkin/show/all', name: 'orgCheckin_how_all_kids', methods: ['GET'])]
    public function getallKids(Request $request, CheckinSchulkindservice $checkinSchulkindservice, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $today = new \DateTime();
        $kinder = $checkinSchulkindservice->getAllKidsToday($organisation, $today, $this->getUser());
        $text = $translator->trans('Kinder Anwesend am %date%', ['%date%' => $today->format('d.m.Y')]);
        $date = new \DateTime();

        return $this->render('checkin_schulkindbetreuung/childList.twig', ['text' => $text, 'kinder' => $kinder, 'date' => $date]);
    }

    #[Route(path: '/org_checkin/show/block', name: 'orgCheckin_how_block_kids', methods: ['GET'])]
    public function getBlockKids(Request $request, CheckinSchulkindservice $checkinSchulkindservice, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        $today = new \DateTime();
        $kinder = $checkinSchulkindservice->getAllKidsTodayandBlock($organisation, $today, $block);
        $text = $translator->trans('Kinder Anwesend im Betreuungszeitfenster am %date% von %time% Uhr für die Schule %schule%',
            [
                '%date%' => $block->getWochentagString(),
                '%time%' => $block->getVon()->format('H:i') . '-' . $block->getBis()->format('H:i'),
                '%schule%' => $block->getSchule()->getName(),
            ]
        );

        return $this->render('checkin_schulkindbetreuung/childList.twig', ['text' => $text, 'kinder' => $kinder]);
    }
}
