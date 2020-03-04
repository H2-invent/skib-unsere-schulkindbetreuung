<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kundennummern;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Schule;
use App\Entity\Sepa;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\customerIDStammdatenType;
use App\Form\Type\SepaStammdatenType;
use App\Form\Type\SepaType;
use App\Service\MailerService;
use App\Service\PrintRechnungService;
use App\Service\SepaCreateService;
use App\Service\SEPASimpleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class SepaController extends AbstractController
{
    /**
     * @Route("/org_accounting/overview", name="accounting_overview",methods={"GET","POST"})
     */
    public function index(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        set_time_limit(600);
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
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
                $result = $sepaCreateService->calcSepa($sepa);

                return $this->redirectToRoute('accounting_overview', array('id' => $organisation->getId(), 'snack' => $result));
            }
        }


        $sepaData = $this->getDoctrine()->getRepository(Sepa::class)->findBy(array('organisation' => $organisation));

        return $this->render('sepa/show.html.twig', array('form' => $form->createView(), 'sepa' => $sepaData));
    }


    /**
     * @Route("/org_accounting/sendBill", name="accounting_send_bill",methods={"GET"})
     */
    public function sendBill(TranslatorInterface $translator, Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        set_time_limit(600);
        $sepa = $this->getDoctrine()->getRepository(Sepa::class)->find($request->get('sepa_id'));
        if ($sepa->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $result = $sepaCreateService->collectallFromSepa($sepa) ? $translator->trans('Die Email wurde erfolgreich versandt') : $translator->trans('Die E-Mail konnte nicht vesandt werden');

        return $this->redirectToRoute('accounting_overview', array('id' => $sepa->getOrganisation()->getId(), 'snack' => $result));

    }

    /**
     * @Route("/org_accounting/showdata", name="accounting_showdata",methods={"GET"})
     */
    public function showStammdaten(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $qb = $this->getDoctrine()->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');
        $qb->innerJoin('stammdaten.kinds', 'kinds')
            ->innerJoin('kinds.schule', 'schule')
            ->andWhere('schule.organisation = :org')
            ->setParameter('org', $organisation);
        if ($request->get('year_id')) {
            $year = $this->getDoctrine()->getRepository(Active::class)->find($request->get('year_id'));
            $qb->innerJoin('kinds.zeitblocks', 'zeitbocks')
                ->andWhere('zeitbocks.active = :year')
                ->setParameter('year', $year);
        };
        $query = $qb->getQuery();
        $stammdaten = $query->getResult();

        return $this->render('sepa/showData.html.twig', array('organisation' => $organisation, 'stammdaten' => $stammdaten));
    }


    /**
     * @Route("/org_accounting/showdata/customerid", name="accounting_showdata_customerid",methods={"GET","POST"})
     */
    public function customerIDStammdaten(Request $request, SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }


        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->find($request->get('stammdaten_id'));

        $kundennummer = $this->getDoctrine()->getRepository(Kundennummern::class)->findOneBy(array('organisation' => $organisation, 'stammdaten' => $stammdaten));
        if (!$kundennummer) {
            $kundennummer = new Kundennummern();
        }

        $form = $this->createForm(customerIDStammdatenType::class, $kundennummer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $kundennummer = $form->getData();
            $kundennummer->setOrganisation($organisation);
            $kundennummer->setStammdaten($stammdaten);

            $em = $this->getDoctrine()->getManager();
            $em->persist($kundennummer);
            $em->flush();

            $response = $this->redirectToRoute('accounting_showdata', array('id' => $organisation->getId()));
            return $response;
        }
        return $this->render('sepa/customerID.html.twig', array('form' => $form->createView(), 'stammdaten' => $stammdaten));

    }

}
