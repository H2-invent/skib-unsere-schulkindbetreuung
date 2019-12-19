<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Form\Type\FerienBlockCustomQuestionType;
use App\Form\Type\FerienBlockPreisType;
use App\Form\Type\FerienBlockType;
use App\Form\Type\OrganisationFerienType;
use App\Service\AnwesenheitslisteService;
use App\Service\CheckinFerienService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class FerienManagementController extends AbstractController
{
    /**
     * @Route("/org_ferien/edit/show", name="ferien_management_show",methods={"GET"})
     */
    public function index(Request $request)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $blocks = $this->getDoctrine()->getRepository(Ferienblock::class)->findBy(array('organisation' => $organisation), array('startDate' => 'asc'));

        return $this->render('ferien_management/index.html.twig', array('blocks' => $blocks, 'org' => $organisation));
    }


    /**
     * @Route("/org_ferien/edit/neu", name="ferien_management_neu", methods={"GET","POST"})
     */
    public function neu(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = new Ferienblock();
        $ferienblock->setOrganisation($organisation);
        $ferienblock->setStadt($organisation->getStadt());
        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('ferien_management_preise', array('ferien_id' => $block->getId(), 'org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ferienprogramm erstellen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }


    /**
 * @Route("/org_ferien/edit/preise", name="ferien_management_preise", methods={"GET","POST"})
 */
    public function preise(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        if ($ferienblock->getPreis() === null || $ferienblock->getNamePreise() === null || sizeof($ferienblock->getPreis()) != $ferienblock->getAnzahlPreise()) {
            $ferienblock->setNamePreise(array_fill(0, $ferienblock->getAnzahlPreise(), ''));
            $ferienblock->setPreis(array_fill(0, $ferienblock->getAnzahlPreise(), 0));
        }

        $form = $this->createForm(FerienBlockPreisType::class, $ferienblock);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Preise bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/org_ferien/edit/question", name="ferien_management_question", methods={"GET","POST"})
     */
    public function ferienblockFragen(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        if (sizeof($ferienblock->getCustomQuestion()) != 5 || $ferienblock->getAmountCustomQuestion() === null) {
            $ferienblock->setCustomQuestion(array_fill(0, 5, ''));
        }

        $form = $this->createForm(FerienBlockCustomQuestionType::class, $ferienblock);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Fragen bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/org_ferien/edit/edit", name="ferien_management_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));

        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ferienprogramm bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }


    /**
     * @Route("/org_ferien/edit/delete", name="ferien_management_delete", methods={"DELETE"})
     */
    public function delte(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        $em = $this->getDoctrine()->getManager();
        $em->remove($ferienblock);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return new JsonResponse(array('redirect' => $this->generateUrl('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text))));
    }


    /**
     * @Route("/org_ferien/duplicate", name="ferien_management_duplicate", methods={"POST"})
     */
    public function duplicate(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        $ferienblockNew = clone $ferienblock;
        $translations = $ferienblock->getTranslations();
        foreach ($translations as $locale => $fields) {
            $clone = clone $fields;
            $clone->setTitel('[copy]' . $clone->getTitel());
            $ferienblockNew->addTranslation($clone);

        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($ferienblockNew);
        $em->flush();
        $text = $translator->trans('Erfolgreich kopiert');
        return new JsonResponse(array('redirect' => $this->generateUrl('ferien_management_edit', array('org_id' => $organisation->getId(), 'ferien_id' => $ferienblockNew->getId(), 'snack' => $text))));

    }


    /**
     * @Route("/org_ferien/checkin/list", name="ferien_management_report_checkinlist", methods={"GET"})
     */
    public function checkinListFerien(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $block = $this->getDoctrine()->getRepository(Ferienblock::class)->find($request->get('ferien_id'));

        if ($organisation != $block->getOrganisation()) {
            throw new \Exception('Organisation not responsible for block');
        }

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime('today');
        $checkinDate = $today->format('Y-m-d');
        $kinder = $this->getDoctrine()->getRepository(KindFerienblock::class)->findBy(array('ferienblock' => $block));
        $titel = $translator->trans('Anwesenheitsliste für Ferienblock');
        $mode = 'block';

        return $this->render('ferien_management/checkinList.html.twig', [
            'org' => $organisation,
            'list' => $kinder,
            'day' => $checkinDate,
            'titel' => $titel,
            'mode' => $mode,
        ]);
    }


    /**
     * @Route("/org_ferien/orders", name="ferien_management_orders", methods={"GET"})
     */
    public function ordersOverview(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $qb = $this->getDoctrine()->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');
        $qb->innerJoin('stammdaten.kinds','kinds')
            ->innerJoin('kinds.kindFerienblocks','kind_ferienblocks')
            ->innerJoin('kind_ferienblocks.ferienblock','ferienblock')
            ->andWhere('ferienblock.organisation = :org')
            ->setParameter('org', $organisation);
        //todo hier muss noch nach nem FIn gefagt werden, die dafür sorgt dass nur fertige Anmeldungen angezeigt werden
        $query = $qb->getQuery();
        $stammdaten = $query->getResult();
        $titel = $translator->trans('Alle Anmeldungen');

        return $this->render('ferien_management/orderList.html.twig', [
            'org' => $organisation,
            'stammdaten' => $stammdaten,
            'titel' => $titel,
        ]);
    }

    /**
     * @Route("/org_ferien/orders/storno", name="ferien_management_orders_storno", methods={"GET"})
     */
    public function storno(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid'=>$request->get('parent_id')));

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        return $this->redirectToRoute('ferien_storno', array('slug'=>$organisation->getStadt()->getSlug(),'parent_id'=>$stammdaten->getUid()));
    }



    /**
     * @Route("/org_ferien/checkin/list/tag", name="ferien_management_report_checkinlist_tag", methods={"GET"})
     */
    public function checkinListTagyFerien(Request $request, TranslatorInterface $translator, AnwesenheitslisteService $anwesenheitslisteService)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $tag = $request->get('tag');
        $selectDate = null;
        if ($tag === null) {
            $today = new \DateTime('today');
            $selectDate = $today->setTime(0, 0);
        } else {
            $selectDate = new \DateTime($tag);
            $selectDate->setTime(0, 0);
        }
        $kind = $anwesenheitslisteService->anwesenheitsListe($selectDate, $organisation);

        $titel = $translator->trans('Anwesenheitsliste');
        $mode = 'day';
        return $this->render('ferien_management/checkinList.html.twig', [
            'org' => $organisation,
            'list' => $kind,
            'day' => $selectDate,
            'titel' => $titel,
            'mode' => $mode,
        ]);
    }


    /**
     * @Route("/org_ferien/checkin/toggle/{checkinID}", name="ferien_management_report_checkin_toggle", methods={"PATCH"})
     */
    public function checkinBlockAction(Request $request, TranslatorInterface $translator, $checkinID, CheckinFerienService $checkinFerienService)
    {
        $result = $checkinFerienService->checkin($checkinID, $request->get('tag'));

        return new JsonResponse($result);
    }


    /**
     * @Route("/org_ferien/orders/detail", name="ferien_management_order_detail", methods={"GET"})
     */
    public function orderDetails(Request $request, TranslatorInterface $translator, AnwesenheitslisteService $anwesenheitslisteService)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $qb = $this->getDoctrine()->getRepository('App:Kind')->createQueryBuilder('kind')
            ->innerJoin('kind.kindFerienblocks', 'kind_ferienblocks')
            ->innerJoin('kind_ferienblocks.ferienblock','ferienblock')
            ->andWhere('ferienblock.organisation = :org')
            ->andWhere('kind.eltern = :stammdaten')
            ->setParameter('org',$organisation)
            ->setParameter('stammdaten',$stammdaten);
        $query= $qb->getQuery();
        $kinds = $query->getResult();
        $titel = $translator->trans('Details');

        return $this->render('ferien_management/details.html.twig', [
            'org' => $organisation,
            'stammdaten' => $stammdaten,
            'titel' => $titel,
            'kinds'=>$kinds,
        ]);
    }


    /**
     * @Route("/org_ferien_admin/edit", name="ferien_admin_edit",methods={"GET","POST"})
     */
    public function ferienOrgEdit(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation()!= $organisation){
            throw new \Exception('Wrong City');
        }

        $form = $this->createForm(OrganisationFerienType::class, $organisation);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $errors = $validator->validate($organisation);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_admin_edit',array('snack'=>$text,'org_id'=>$organisation->getId()));
            }

        }
        $title = $translator->trans('Ferieneinstellungen ändern');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }

}
