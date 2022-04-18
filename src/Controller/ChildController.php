<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Service\ChildExcelService;
use App\Service\ChildSearchService;
use App\Service\MailerService;
use App\Service\PrintService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use function Doctrine\ORM\QueryBuilder;

class ChildController extends AbstractController
{
    private $wochentag;
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->wochentag = [
            $this->translator->trans('Montag'),
            $this->translator->trans('Dienstag'),
            $this->translator->trans('Mittwoch'),
            $this->translator->trans('Donnerstag'),
            $this->translator->trans('Freitag'),
            $this->translator->trans('Samstag'),
            $this->translator->trans('Sonntag'),
        ];
    }

    /**
     * @Route("/org_child/show", name="child_show")
     */
    public function showAction(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $text = $translator->trans('Kinder betreut vom Träger');
        $schulen = $organisation->getSchule()->toArray();
        $schuljahre = $schulen[0]->getStadt()->getActives()->toArray();


        return $this->render('child/child.html.twig', [


            'organisation' => $organisation,
            'schuljahre' => $schuljahre,
            'text' => $text
        ]);
    }

    /**
     * @Route("/org_child/show/detail", name="child_detail")
     */
    public function childDetail(Request $request, TranslatorInterface $translator, LoerrachWorkflowController $loerrachWorkflowController)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));
        $history = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('tracing' => $kind->getTracing(), 'saved' => true));
        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        return $this->render('child/childDetail.html.twig', array('beruflicheSituation' => array_flip($loerrachWorkflowController->beruflicheSituation), 'k' => $kind, 'eltern' => $kind->getEltern(), 'history' => $history));
    }

    /**
     * @Route("/org_child/print/detail", name="child_detail_print")
     */
    public function printChild(Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $fileName = $kind->getVorname() . '_' . $kind->getNachname();
        return $printService->printChildDetail($kind, $kind->getEltern(), $TCPDFController, $fileName, $kind->getSchule()->getOrganisation(), 'D');
    }

    /**
     * @Route("/org_child/search/child/table", name="child_child_Table",methods={"GET","POST"})
     */
    public function buildChildTable(ChildSearchService $childSearchService, Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController, ChildExcelService $childExcelService)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('organisation'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $parameter = null;
        if ($request->isMethod('POST')) {
            $parameter = $request->request->all();
        } else {
            $parameter = $request->query->all();
        }

        $fileName = $translator->trans('Kinder') .' - ' . (new \DateTime())->format('d.m.Y_H.i');

        $text = $translator->trans('Kinder betreut vom Träger %organisation%', array('%organisation%' => $organisation->getName()));
        if ($request->get('schule')) {
            $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule'));
            $text .= $translator->trans(' an der Schule %schule%', array('%schule%' => $schule->getName()));
        }
        if ($request->get('schuljahr')) {
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $text .= $translator->trans(' im Schuljahr schuljahr', array('schuljahr' => $jahr->getVon()->format('d.m.Y') . '-' . $jahr->getBis()->format('d.m.Y')));
        }
        if ($request->get('wochentag') !== "") {
            $text .= $translator->trans(' am Wochentag %wochentag%', array('%wochentag%' => $this->wochentag[$request->get('wochentag')]));
        }
        if ($request->get('block')) {
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block'));
            $fileName = $block->getSchule()->getName() . '-' . $block->getWochentagString() . '-' . $block->getVon()->format('H:i') . '-' . $block->getBis()->format('H:i');
            $text .= $translator->trans(' am %d% von %s% bis %e% Uhr', array('%d%' => $block->getWochentagString(), '%s%' =>$block->getVon()->format('H:i'), '%e%' =>$block->getBis()->format('H:i') ));
        }
        if ($request->get('klasse')) {
            $text .= $translator->trans(' in der Klasse: %klasse%', array('%klasse%' => $request->get('klasse')));
        }

        $kinderU = $childSearchService->searchChild($parameter, $organisation, false, $this->getUser());


        if ($request->get('print')) {
            return $printService->printChildList($kinderU, $organisation, $text, $fileName, $TCPDFController, 'D');

        } elseif ($request->get('spread')) {
            return $this->file($childExcelService->generateExcel($kinderU), $fileName . '.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
        } else {
            return $this->render('child/childTable.html.twig', [
                'kinder' => $kinderU,
                'text' => $text
            ]);
        }


    }

    /**
     * @Route("/org_child/change/resend", name="child_resend_SecCode")
     */
    public function resendSecCodeChild(Request $request, TranslatorInterface $translator, MailerService $mailerService)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        try {
            $title = $translator->trans('Email mit Sicherheitscode');
            $content = $this->renderView('email/resendSecCode.html.twig', array('eltern' => $kind->getEltern(), 'stadt' => $kind->getSchule()->getStadt()));
            $mailerService->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $kind->getEltern()->getEmail(),
                $title,
                $content,
                $kind->getSchule()->getOrganisation()->getEmail());
            $text = $translator->trans('Sicherheitscode erneut zugesendet');
        } catch (\Exception $exception) {
            $text = $translator->trans('Sicherheitscode konnte nicht erneut zugesendet');
        }
        return $this->redirectToRoute('child_show', ['id' => $kind->getSchule()->getOrganisation()->getId(), 'snack' => $text]);
    }
}
