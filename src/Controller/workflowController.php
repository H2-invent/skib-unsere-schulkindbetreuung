<?php
namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\StadtType;
use App\Service\MailerService;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use phpDocumentor\Reflection\Types\This;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class workflowController  extends AbstractController
{
    /**
    * @Route("/{slug}/start",name="workflow_start",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
    */
    public function welcomeAction(Request $request, Stadt $stadt)
    {
        $url = '';
        switch($stadt->getSlug()){
            case 'loerrach':
           $url = $this->generateUrl('loerrach_workflow_adresse');
           break;
            default:
                break;

        }
        return $this->render('workflow/start.html.twig',array('stadt'=>$stadt,'url'=>$url));
    }


    /**
     * @Route("/{slug}/closed",name="workflow_closed",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function closedAction(Request $request, Stadt $stadt)
    {

        return $this->render('workflow/closed.html.twig',array('stadt'=>$stadt));
    }
    /**
     * @Route("/confirmEmail",name="workflow_confirm_Email",methods={"GET","POST"})
     */
    public function confirmAction(Request $request, MailerService $mailer,TranslatorInterface $translator)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid'=>$request->get('uid')));
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt'));
        if($stammdaten->getEmailConfirmed()){
           return $this->redirect($request->get('redirect'));
        }else{
            dump('test');
            if($stammdaten->getConfirmationCode() == null){
                $stammdaten->setConfirmationCode(substr(str_shuffle(MD5(microtime())), 0, 6));
                $em = $this->getDoctrine()->getManager();
                $em->persist($stammdaten);
                $em->flush();
            }
            $formData = array('confirmationCode'=>'',
                'redirectUrl'=>$request->get('redirect'));
              $form = $this->createFormBuilder($formData)
                ->add('confirmationCode', TextType::class, ['label' => 'Bestätigungscode', 'translation_domain' => 'form'])
                ->add('redirectUrl',HiddenType::class)
                ->add('submit', SubmitType::class, ['attr'=> array('class'=> 'btn btn-outline-primary'), 'label' => 'weiter', 'translation_domain' => 'form'])
                ->getForm();
            $form->handleRequest($request);
            dump($formData);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData= $form->getData();
                if($formData['confirmationCode'] == $stammdaten->getConfirmationCode()){
                    $stammdaten->setEmailConfirmed(true);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($stammdaten);
                    $em->flush();
                    return $this->redirect($formData['redirectUrl']);
                }
               return $this->redirectToRoute('workflow_confirm_Email',array('stadt'=>$stadt->getId(),'uid'=>$stammdaten->getUid(),'redirect'=>$formData['redirectUrl'],'snack'=>$translator->trans('Bestätigungscode fehlerhaft')));
            }
            $mailBetreff = $translator->trans('Bestätigungscode für die Schulbetreuungsanmeldung ');
            $mailContent = $this->renderView('email/bestaetigungscode.html.twig',array('eltern'=>$stammdaten));
            $mailer->sendEmail( 'info@h2-invent.com', $stammdaten->getEmail(), $mailBetreff, $mailContent);
            $text= $translator->trans('Wir haben Ihnen einen Bestätigungscode an Ihre Emailadresse gesandt. Bitte geben Sie diesen Code aus der Email hier ein. Dies ist notwendig um die Daten Ihrer Kinder bestmöglich zu schützen.');

            return $this->render('workflow/form.html.twig',array('form'=>$form->createView(),'titel'=>$mailBetreff,'text'=>$text,'stadt'=>$stadt));
        }
    }
}