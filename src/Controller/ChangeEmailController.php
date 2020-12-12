<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Log;
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
    public function index(TranslatorInterface $translator, Request $request)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()){
            $text = $translator->trans('Keine Berechtigung');
            return $this->redirectToRoute('child_show',array('id'=>$this->getUser()->getOrganisation()->getId(),'snack'=>$text));
        }


        $input = array('email'=>'','emailDoubleInput'=>'');

        $form = $this->createFormBuilder($input)
            ->add('email', TextType::class, ['label' => 'Neue Email Adresse eingeben', 'translation_domain' => 'form'])
            ->add('emailDoubleInput', TextType::class, ['label' => 'Neue Email Adresse erneut eingeben', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr'=> array('class'=> 'btn btn-outline-primary'), 'label' => 'Speichern', 'translation_domain' => 'form'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $input = $form->getData();

            if ($input['email'] === $input['emailDoubleInput']){

                $kinder= $this->getDoctrine()->getRepository(Kind::class)->findBy(array('tracing'=>$kind->getTracing()));
                $message = 'Email addresse from '. $kinder[0]->getEltern()->getEmail() . ' to ' . $input['email'];
                $log = new Log();
                $log->setUser($this->getUser()->getEmail());
                $log->setDate(new \DateTime());
                $log->setMessage($message);
                $this->getDoctrine()->getManager()->persist($log);
                foreach ($kinder as $data) {
                    $data->getEltern()->setEmail($input['email']);
                    $data->getEltern()->setEmailDoubleInput($input['emailDoubleInput']);
                    $this->getDoctrine()->getManager()->persist($data);
                }
                $this->getDoctrine()->getManager()->flush();
                //todo: Hier muss noch der Email Service aufgerufen werden.

                $text = $translator->trans('Email adresse geändert');
                return $this->redirectToRoute('child_show',array('id'=>$this->getUser()->getOrganisation()->getId(),'snack'=>$text));
            }else{
                $text = $translator->trans('Email Adressen stimmen nicht überein.');
                return $this->redirectToRoute('org_child_email_change',array('kind_id'=>$kind->getId(),'snack'=>$text));

            }
        }

        return $this->render('child_change/email.html.twig', array('form' => $form->createView()));
    }
}
