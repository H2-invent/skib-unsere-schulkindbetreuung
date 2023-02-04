<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\InternNoticeType;
use App\Service\ChildExcelService;
use App\Service\ChildSearchService;
use App\Service\ElternService;
use App\Service\HistoryService;
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
    public static $WEEKDAY = [
        'Montag',
        'Dienstag',
        'Mittwoch',
        'Donnerstag',
        'Freitag',
        'Samstag',
        'Sonntag',
    ];

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
        $schulen = sizeof($this->getUser()->getSchulen()) === 0 ? $organisation->getSchule()->toArray() : $this->getUser()->getSchulen();
        $schuljahre = $schulen[0]->getStadt()->getActives()->toArray();
        $actualSchuljahr = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($organisation->getStadt());

        if ($request->get('block')) {
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block'));
            $actualSchuljahr = $block->getActive();
        }

        return $this->render('child/child.html.twig', [
            'actualSchuljahr' => $actualSchuljahr,
            'organisation' => $organisation,
            'schuljahre' => $schuljahre,
            'schulen' => $schulen,
            'text' => $text
        ]);
    }

    /**
     * @Route("/org_child/show/detail", name="child_detail")
     */
    public function childDetail(Request $request, TranslatorInterface $translator, LoerrachWorkflowController $loerrachWorkflowController, ElternService $elternService, HistoryService $historyService)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        $date = new \DateTime();
        if ($request->get('date')) {
            $date = new \DateTime($request->get('date'));
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findLatestKindForDate($kind, $date);
        $eltern = $elternService->getElternForSpecificTimeAndKind($kind, $date);
        $historydate = $historyService->getAllHistoyPointsFromKind($kind);

        $form = $this->createForm(InternNoticeType::class, $kind, array('action' => $this->generateUrl('child_detail_save_internal', array('childId' => $kind->getId()))));
        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        return $this->render('child/childDetail.html.twig', array('beruflicheSituation' => array_flip($loerrachWorkflowController->beruflicheSituation), 'k' => $kind, 'eltern' => $eltern, 'his' => $historydate, 'date' => $date, 'history' => $historydate, 'formInternalNotice' => $form->createView()));
    }

    /**
     * @Route("/org_child/show/detail/save/internal/notice/{childId}", name="child_detail_save_internal")
     */
    public function childSaveInternall(Request $request, TranslatorInterface $translator, LoerrachWorkflowController $loerrachWorkflowController, $childId)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($childId);
        if (!$kind || $kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $form = $this->createForm(InternNoticeType::class, $kind);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($kind);
            $em->flush();
            $notice = $kind->getInternalNotice();
            $kinder = $em->getRepository(Kind::class)->findBy(array('tracing' => $kind->getTracing()));
            foreach ($kinder as $data) {
                $data->setInternalNotice($notice);
                $em->persist($data);

            }
            $em->flush();
        }

        return $this->redirectToRoute('child_detail', array('kind_id' => $kind->getId()));
    }


    /**
     * @Route("/org_child/print/detail", name="child_detail_print")
     */
    public function printChild(Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController, ElternService $elternService)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));
        $date = new \DateTime();
        if ($request->get('date')) {
            $date = new \DateTime($request->get('date'));
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findLatestKindForDate($kind, $date);
        $eltern = $elternService->getElternForSpecificTimeAndKind($kind, $date);

        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $fileName = $kind->getVorname() . '_' . $kind->getNachname();
        return $printService->printChildDetail($kind, $eltern, $TCPDFController, $fileName, $kind->getSchule()->getOrganisation(), 'D');
    }

    /**
     * @Route("/org_child/search/child/table", name="child_child_Table",methods={"GET","POST"})
     */
    public function buildChildTable(ChildSearchService $childSearchService, Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController, ChildExcelService $childExcelService)
    {
        set_time_limit(300);
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

        $fileName = $translator->trans('Kinder') . ' - ' . (new \DateTime())->format('d.m.Y_H.i');
        $startDate = isset($parameter['startDate']) ? new \DateTime($parameter['startDate']) : null;
        $endDate = isset($parameter['endDate']) ? new \DateTime($parameter['endDate']) : null;

        $text = $translator->trans('Kinder betreut vom Träger %organisation%', array('%organisation%' => $organisation->getName()));
        if ($request->get('schule')) {
            $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule'));
            $text .= $translator->trans(' an der Schule %schule%', array('%schule%' => $schule->getName()));
        }
        if ($request->get('schuljahr')) {
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $text .= $translator->trans(' im Schuljahr schuljahr', array('schuljahr' => $jahr->getVon()->format('d.m.Y') . '-' . $jahr->getBis()->format('d.m.Y')));
            if ($jahr && $jahr->getVon() > new \DateTime()){
                $startDate= $jahr->getVon();
            }
        }
        if ($request->get('wochentag') !== "") {
            $text .= $translator->trans(' am Wochentag %wochentag%', array('%wochentag%' => $this->wochentag[$request->get('wochentag')]));
        }
        if ($request->get('block')) {
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block'));
            $fileName = $block->getSchule()->getName() . '-' . $block->getWochentagString() . '-' . $block->getVon()->format('H:i') . '-' . $block->getBis()->format('H:i');
            $text .= $translator->trans(' am %d% von %s% bis %e% Uhr', array('%d%' => $block->getWochentagString(), '%s%' => $block->getVon()->format('H:i'), '%e%' => $block->getBis()->format('H:i')));
        }
        if ($request->get('klasse')) {
            $text .= $translator->trans(' in der Klasse: %klasse%', array('%klasse%' => $request->get('klasse')));
        }

        $kinderU = $childSearchService->searchChild($parameter, $organisation, false, $this->getUser(), $startDate, $endDate);
        usort($kinderU, function (Kind $a, Kind $b): int {
            if ($a->getKlasse() === $b->getKlasse()) {
                return strtolower($a->getNachname()) <=> strtolower($b->getNachname());
            }
            return $a->getKlasse() <=> $b->getKlasse();
        });

        if ($request->get('print')) {

            return $printService->printChildList($kinderU, $organisation, $text, $fileName, $TCPDFController, $request->get('wochentag') !== "" ? [$request->get('wochentag')] : [0, 1, 2, 3, 4], 'D');

        } elseif ($request->get('spread')) {
            return $this->file($childExcelService->generateExcel($kinderU, $organisation->getStadt(), $request->get('wochentag') !== "" ? $request->get('wochentag') : null,), $fileName . '.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);

        } else {
            return $this->render('child/childTable.html.twig', [
                'kinder' => $kinderU,
                'text' => $text,
                'date' => $endDate ?: $startDate
            ]);
        }


    }

    /**
     * @Route("/org_child/change/resend", name="child_resend_SecCode")
     */
    public function resendSecCodeChild(Request $request, TranslatorInterface $translator, MailerService $mailerService, ElternService $elternService)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        try {
            $title = $translator->trans('Email mit Sicherheitscode');
            $eltern = $kind->getEltern();
            if (!$eltern->getSecCode()){
                $elternAll = $this->getDoctrine()->getRepository(Stammdaten::class)->findBy(array('tracing'=>$eltern->getTracing()));
                $secCode = substr(str_shuffle(MD5(microtime())), 0, 6);
                $em = $this->getDoctrine()->getManager();
                foreach ($elternAll as $data){
                    $data->setSecCode($secCode);
                    $em->persist($data);
                }
                $em->flush();
            }
            $content = $this->renderView('email/resendSecCode.html.twig', array('eltern' => $eltern, 'stadt' => $kind->getSchule()->getStadt()));
            $mailerService->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $elternService->getLatestElternFromChild($kind)->getEmail(),
                $title,
                $content,
                $kind->getSchule()->getOrganisation()->getEmail());
            $text = $translator->trans('Sicherheitscode erneut zugesendet');
        } catch (\Exception $exception) {

            $text = $translator->trans('Sicherheitscode konnte nicht erneut zugesendet');
        }
        return $this->redirectToRoute('child_show', ['id' => $kind->getSchule()->getOrganisation()->getId(), 'snack' => $text]);
    }

    /**
     * @Route("/org_child/addNew", name="child_add_new")
     */
    public function addNewChild(Request $request, TranslatorInterface $translator, MailerService $mailerService, ElternService $elternService)
    {

        $response = $this->redirectToRoute('workflow_start', array('slug' => $this->getUser()->getOrganisation()->getStadt()->getSlug()));
        $response->headers->clearCookie('KindID');
        $response->headers->clearCookie('SecID');
        $response->headers->clearCookie('UserID');
        return $response;
    }
}
