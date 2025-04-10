<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Form\Type\FormelType;
use App\Form\Type\StadtType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StadtverwaltungController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/admin/index", name="admin_index",methods={"GET"})
     */
    public function index()
    {
        return $this->render('administrator/index.html.twig', [
            'controller_name' => 'AdministratorController',
        ]);
    }

    /**
     * @Route("/admin/stadtverwaltung", name="admin_stadt", methods={"GET"})
     */
    public function stadtverwaltung()
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->findBy(array('deleted' => false));

        return $this->render('administrator/stadt.html.twig', [
            'city' => $city
        ]);
    }

    /**
     * @Route("/admin/stadtverwaltung/neu", name="admin_stadt_neu",methods={"GET","POST"} )
     */
    public function newStadt(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $city = new Stadt();

        if($city->getGehaltsklassen() === null || $city->getGehaltsklassen() === null || sizeof($city->getGehaltsklassen()) != $city->getPreiskategorien()){
            $city->setGehaltsklassen(array_fill(0,$city->getPreiskategorien(), ''));
        }
        $form = $this->createForm(StadtType::class, $city);

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $city->setCreatedAt(new \DateTime());
            $errors = $validator->validate($city);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($city);
                $em->flush();
                return $this->redirectToRoute('admin_stadt');
            }

        }
        $title = $translator->trans('Stadt anlegen');
        return $this->render('administrator/stadtForm.html.twig', array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors));
    }

    /**
     * @Route("/city_edit/stadtverwaltung/edit", name="admin_stadt_edit",methods={"GET","POST"} )
     */
    public function editStadt(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));

        if($city->getGehaltsklassen() === null || $city->getGehaltsklassen() === null || sizeof($city->getGehaltsklassen()) != $city->getPreiskategorien()){
            $city->setGehaltsklassen(array_fill(0,$city->getPreiskategorien(), ''));
        }

        $form = $this->createForm(StadtType::class, $city);
        $form->remove('slug');
        if (!$this->getUser()->hasRole('ROLE_ADMIN')){
           $form->remove('schulkindBetreung');
           $form->remove('ferienprogramm');
            $form->remove('active');
            $form->remove('settingEncryptEmailAttachment');
       }

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $errors = $validator->validate($city);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($city);

                //wichtig vor dem Flush
                $city->mergeNewTranslations();
                $em->flush();
                return $this->redirectToRoute('admin_stadt_edit', array('id' => $city->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }

        }
        $title = $translator->trans('Stadt bearbeiten');
        return $this->render('administrator/stadtForm.html.twig', array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors));
    }

    /**
     * @Route("/admin/stadtverwaltung/delete", name="admin_stadt_delete", methods={"GET"})
     */
    public function deleteStadt(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $city->setDeleted(true);
        $em = $this->managerRegistry->getManager();
        $em->persist($city);
        $em->flush();
        return $this->redirectToRoute('admin_stadt');
    }

    /**
     * @Route("/admin/berechner", name="admin_berechner",methods={"GET","POST"})
     */
    public function berechnerEdit(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $form = $this->createForm(FormelType::class, $city);
        $form->handleRequest($request);
        $error = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();

            $em = $this->managerRegistry->getManager();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('admin_berechner', array('id' => $city->getId()));
        }


        return $this->render('administrator/neu.html.twig', [
            'form' =>$form->createView(),
            'title'=>'Berechnungsformel',
            'errors'=>$error
        ]);
    }
}
