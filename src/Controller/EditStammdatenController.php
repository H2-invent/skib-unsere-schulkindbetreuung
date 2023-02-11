<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\LoerrachEltern;
use App\Service\ErrorService;
use App\Service\SchulkindBetreuungAdresseService;
use App\Service\StammdatenEditEmailService;
use App\Service\WorkflowAbschluss;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditStammdatenController extends AbstractController
{
    /**
     * @Route("/org_child/stammdaten/edit/seccode", name="edit_stammdaten_seccode")
     */
    public function index(Request $request, TranslatorInterface $translator): Response
    {

        $adresseTmp = $this->getDoctrine()->getRepository(Stammdaten::class)->find($request->get('eltern_id'));
        $adresse = $this->getDoctrine()->getRepository(Stammdaten::class)->findWorkingCopyStammdatenByStammdaten($adresseTmp);
        $input = array('seccode' => '');

        $form = $this->createFormBuilder($input)
            ->add('seccode', TextType::class, ['label' => 'Sicherheitscode des Erziehungsberechtigten', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'weiter', 'translation_domain' => 'form'])
            ->getForm();

        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || $this->getUser()->getOrganisation()->getStadt()->getNoSecCodeForChangeChilds()) {

            $input = $form->getData();

            if ($input['seccode'] == $adresseTmp->getSecCode() || $this->getUser()->getOrganisation()->getStadt()->getNoSecCodeForChangeChilds()) {


                return $this->redirectToRoute('edit_stammdaten_edit', array('eltern_id' => $adresse->getId()));

            } else {
                $text = $translator->trans('Sicherheitscode ungültig');
                return $this->redirectToRoute('edit_stammdaten_seccode', array('eltern_id' => $adresseTmp->getId(), 'snack' => $text));

            }
        }

        return $this->render('child_change/seccode.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/org_child/stammdaten/edit/data/{eltern_id}", name="edit_stammdaten_edit")
     */
    public function edit(
        Request                          $request,
        TranslatorInterface              $translator,
                                         $eltern_id,
        LoerrachWorkflowController       $loerrachWorkflowController,
        ErrorService                     $errorService,
        ValidatorInterface               $validator,
        SchulkindBetreuungAdresseService $schulkindBetreuungAdresseService,
        WorkflowAbschluss                $abschluss,
        StammdatenEditEmailService       $stammdatenEditEmailService
    ): Response
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->find($eltern_id);
        $workingCopyStammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findWorkingCopyStammdatenByStammdaten($stammdaten);


        $stadt = $this->getUser()->getOrganisation()->getStadt();
        $schuljahr = $this->getDoctrine()->getRepository(Active::class)->findSchuljahrFromStamdaten($workingCopyStammdaten);

        $nextDateTmp = new \DateTime();
        if ($nextDateTmp < $schuljahr->getVon()){
           $nextDate = clone $schuljahr->getVon();
        }else{
            $nextDate = (clone $nextDateTmp)->modify($stadt->getSettingSkibDefaultNextChange());
        }

        $stammdaten->setStartDate($nextDate);
        $formArr = array('einkommen' => array_flip($stadt->getGehaltsklassen()), 'beruflicheSituation' => $loerrachWorkflowController->beruflicheSituation, 'stadt' => $stadt);

        $form = $this->createForm(LoerrachEltern::class, $stammdaten, $formArr);
        $form->remove('emailDoubleInput');
        $form->remove('email');
        $form->remove('gdpr');
        $form->remove('submit');
        $form->add('iban', TextType::class, ['required' => true, 'label' => 'IBAN für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('bic', TextType::class, ['required' => true, 'label' => 'BIC für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('kontoinhaber', TextType::class, ['required' => true, 'label' => 'Kontoinhaber für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);

        if ($stadt->getSettingsSkibShowSetStartDateOnChange()) {
            $form->add('startDate', DateType::class, array(
                    'widget' => 'single_text',
                    'label' => 'Elterndaten gelten ab dem:', 'required' => true,
                    'translation_domain' => 'form',
                    'attr' => array(
                        'min' => $nextDateTmp->format('Y-m-d'),
                        'max' => $schuljahr->getBis()->format('Y-m-d')
                    )
                )
            );
        }
        $form->handleRequest($request);
        $errorsString = array();

        if ($form->isSubmitted()) {
            set_time_limit(6000);
            $adresse = $form->getData();

            $errors = $validator->validate($adresse, null, ['Default', 'internal']);


            $errorsString = $errorService->createError($errors, $form);


            if (count($errors) == 0) {
                $adresse->setCreatedAt(new \DateTime());
                $schulkindBetreuungAdresseService->setAdress($adresse, true, $request->getClientIp());
                $abschluss->abschluss($adresse, $this->getUser()->getOrganisation()->getStadt(), null, true);
                $stammdatenEditEmailService->sendEmail($adresse,$this->getUser()->getOrganisation(),'');
                $stammdatenEditEmailService->send($stammdaten,$this->getUser()->getOrganisation());
                return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $translator->trans('Stammdaten gespeichert')));

            }

        }

        return $this->render('edit_stammdaten/index.html.twig', array('title' => ' Stammdaten bearbeiten', 'stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errorsString));
    }
}
