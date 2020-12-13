<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Log;
use App\Entity\Stammdaten;
use App\Service\AnmeldeEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeEmailController extends AbstractController
{
    /**
     * @Route("/org_child/email_change/", name="org_child_email_change")
     */
    public function index(TranslatorInterface $translator, Request $request, AnmeldeEmailService $anmeldeEmailService)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Keine Berechtigung');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));
        }


        $input = array('email' => $kind->getEltern()->getEmail(), 'emailDoubleInput' => '');

        $form = $this->createFormBuilder($input)
            ->add('email', TextType::class, ['label' => 'Neue Email Adresse eingeben', 'translation_domain' => 'form'])
            ->add('emailDoubleInput', TextType::class, ['label' => 'Neue Email Adresse erneut eingeben', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'Speichern', 'translation_domain' => 'form'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $input = $form->getData();

            if ($input['email'] !== $input['emailDoubleInput']) {
                $text = $translator->trans('Email Adressen stimmen nicht überein.');
                return $this->redirectToRoute('org_child_email_change', array('kind_id' => $kind->getId(), 'snack' => $text));
            }

            $elternOne = $kind->getEltern();
            $elternAll = $this->getDoctrine()->getRepository(Stammdaten::class)->findBy(array('tracing' => $elternOne->getTracing()));

            $kinder = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('tracing' => $kind->getTracing()));
            $message = 'Email addresse changed from ' . $elternOne->getEmail() . ' to ' . $input['email'] . '; ' .
                'id: ' . $elternOne->getId() . '; ' . 'Tracing: ' . $elternOne->getTracing();
            $log = new Log();
            $log->setUser($this->getUser()->getEmail());
            $log->setDate(new \DateTime());
            $log->setMessage($message);
            $this->getDoctrine()->getManager()->persist($log);
            foreach ($elternAll as $data) {
                $data->setEmail($input['email']);
                $data->setEmailDoubleInput($input['emailDoubleInput']);
                $this->getDoctrine()->getManager()->persist($data);
            }
            $this->getDoctrine()->getManager()->flush();
            foreach ($elternOne->getKinds() as $data2) {
                $anmeldeEmailService->sendEmail($data2, $elternOne, $data2->getSchule()->getStadt(), $translator->trans('Ihre E-Mail Adresse wurde von einem Mitarbeiter der betreuenden Organisation geändert. Aus diesem Grund senden wir Ihnen die Buchungsbstätigung dieses Kindes nochmals zu:', [], $elternOne->getLanguage()));
                $anmeldeEmailService->setBetreff($translator->trans('Ihre E-Mail Adresse wurde von einem Mitarbeiter der betreuenden Oranisation geändert.', [], $elternOne->getLanguage()));
                $anmeldeEmailService->send($data2, $data2->getEltern());
            }


            $text = $translator->trans('Email adresse geändert');
            return $this->redirectToRoute('child_show', array('id' => $this->getUser()->getOrganisation()->getId(), 'snack' => $text));

        }
        return $this->render('child_change/email.html.twig', array('form' => $form->createView()));
    }
}
