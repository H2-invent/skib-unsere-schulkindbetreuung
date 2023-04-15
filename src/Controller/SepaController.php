<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Kundennummern;
use App\Entity\Organisation;
use App\Entity\Sepa;
use App\Entity\Stammdaten;
use App\Form\Type\customerIDStammdatenType;
use App\Form\Type\SepaType;
use App\Service\ElternService;
use App\Service\HistoryService;
use App\Service\SepaCreateService;
use App\Twig\Eltern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class SepaController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager, private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/org_accounting/overview", name="accounting_overview",methods={"GET","POST"})
     */
    public function index(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        set_time_limit(600);
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $sepa = new Sepa();
        $sepa->setOrganisation($organisation);
        $form = $this->createForm(SepaType::class, $sepa);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($sepa);
            if (count($errors) == 0) {
                $sepa = $form->getData();
                $sepa->setBis((clone $sepa->getVon())->modify('last day of this month'));
                $result = $sepaCreateService->createSepa($sepa);
                return $this->redirectToRoute('accounting_overview', array('id' => $organisation->getId(), 'snack' => $result));
            }
        }


        $sepaData = $this->managerRegistry->getRepository(Sepa::class)->findBy(array('organisation' => $organisation));

        return $this->render('sepa/show.html.twig', array('form' => $form->createView(), 'sepa' => $sepaData));
    }


    /**
     * @Route("/org_accounting/sendBill", name="accounting_send_bill",methods={"GET"})
     */
    public function sendBill(TranslatorInterface $translator, Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        set_time_limit(600);
        $sepa = $this->managerRegistry->getRepository(Sepa::class)->find($request->get('sepa_id'));
        if ($sepa->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $result = $sepaCreateService->collectallFromSepa($sepa) ? $translator->trans('Die Email wurde erfolgreich versandt') : $translator->trans('Die E-Mail konnte nicht vesandt werden');

        return new JsonResponse(array('redirect' => $this->generateUrl('accounting_overview', array('id' => $sepa->getOrganisation()->getId(), 'snack' => $result))));

    }

    /**
     * @Route("/org_accounting/showdata", name="accounting_showdata",methods={"GET"})
     */
    public function showStammdaten(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator, ElternService $elternService)
    {
        set_time_limit(6000);
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $qb = $this->managerRegistry->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');
        $qb->innerJoin('stammdaten.kinds', 'kinds')
            ->innerJoin('kinds.schule', 'schule')
            ->andWhere('schule.organisation = :org')->setParameter('org', $organisation)
            ->andWhere($qb->expr()->isNotNull('kinds.startDate'))
            ->andWhere($qb->expr()->isNotNull('stammdaten.startDate'))
            ->andWhere($qb->expr()->isNotNull('stammdaten.created_at'));
        if (!$request->get('year_id')) {
            $year = $this->managerRegistry->getRepository(Active::class)->findActiveSchuljahrFromCity($this->getUser()->getOrganisation()->getStadt());
        }else {
            if ($request->get('year_id') == 'all') {
                $year = null;
            }else{
                $year = $this->managerRegistry->getRepository(Active::class)->find($request->get('year_id'));
            }
        }

        if ($year) {

            $qb->innerJoin('kinds.zeitblocks', 'zeitbocks')
                ->andWhere('zeitbocks.active = :year')
                ->setParameter('year', $year);
        };
        $qb->orderBy('stammdaten.startDate', 'ASC');
        $query = $qb->getQuery();
        $stammdaten = $query->getResult();
        $sRes = array();
        foreach ($stammdaten as $data) {
            if (!isset($sRes[$data->getTracing()])) {
                $sRes[$data->getTracing()] = $elternService->getLatestElternFromCEltern($data);
            }
        }

        return $this->render('sepa/showData.html.twig', array('organisation' => $organisation, 'stammdaten' => $sRes,'schuljahr'=>$year));
    }

    /**
     * @Route("/org_accounting/showdata/showMontly/{stammdatenId}", name="accounting_showdata_montly",methods={"GET"})
     */
    public function showStammdatenMontyly(HistoryService $historyService, $stammdatenId, ElternService $elternService)
    {
        $stammdaten = $this->em->getRepository(Stammdaten::class)->find($stammdatenId);
        $history = $historyService->getAllHistoyPointsFromStammdaten($stammdaten);

        $stammdatenArray = array();
        foreach ($history as $data) {
            $tmp = array('date' => $data, 'stammdaten' => $this->em->getRepository(Stammdaten::class)->findStammdatenFromStammdatenByDate($stammdaten, $data), 'kinder' => $elternService->getKinderProStammdatenAnEinemZeitpunkt($stammdaten, $data));
            $stammdatenArray[] = $tmp;
        }

        return $this->render('sepa/showDataMontly.html.twig', array('history' => $stammdatenArray, 'stammdaten' => $stammdaten));
    }

    /**
     * @Route("/org_accounting/showdata/customerid", name="accounting_showdata_customerid",methods={"GET","POST"})
     */
    public function customerIDStammdaten(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }


        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->find($request->get('stammdaten_id'));

        $kundennummer = $this->managerRegistry->getRepository(Kundennummern::class)->findOneBy(array('organisation' => $organisation, 'stammdaten' => $stammdaten));
        if (!$kundennummer) {
            $kundennummer = new Kundennummern();
        }

        $form = $this->createForm(customerIDStammdatenType::class, $kundennummer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $kundennummern = $this->em->getRepository(Kundennummern::class)->findAllKundennummernByStammdatenAndOrganisation($stammdaten,$organisation);
            foreach ($kundennummern as $data) {
                $this->em->remove($data);

            }
            $this->em->flush();;
            $kundennummer = $form->getData();
            $kundennummer->setOrganisation($organisation);
            $kundennummer->setStammdaten($stammdaten);

            $stammdaten = $this->em->getRepository(Stammdaten::class)->findBy(array('tracing'=>$stammdaten->getTracing()));
            foreach ($stammdaten as $data){
               $kdn = clone $kundennummer;
               $kdn->setOrganisation($organisation);
               $kdn->setStammdaten($data);
               $this->em->persist($kdn);
            }
            $this->em->flush();

            $response = $this->redirectToRoute('accounting_showdata', array('id' => $organisation->getId()));
            return $response;
        }
        return $this->render('sepa/customerID.html.twig', array('form' => $form->createView(), 'stammdaten' => $stammdaten));

    }

}
