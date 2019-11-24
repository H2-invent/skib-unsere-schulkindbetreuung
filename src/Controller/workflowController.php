<?php

namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\StadtType;
use App\Service\ConfirmEmailService;
use App\Service\MailerService;
use App\Service\PrintAGBService;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use phpDocumentor\Reflection\Types\This;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class workflowController extends AbstractController
{
    /**
     * @Route("/{slug}/home",name="workflow_start",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function welcomeAction(Request $request, Stadt $stadt)
    {
        $url = '';
        switch ($stadt->getSlug()) {
            case 'loerrach':
                $url = $this->generateUrl('loerrach_workflow_adresse');
                break;
            default:
                break;

        }
        $cityInfoText = $stadt->translate()->getInfoText();
        // Load all schools from the city into the controller as $schulen
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('stadt' => $stadt, 'deleted' => false));

        return $this->render('workflow/start.html.twig', array('schule' => $schule, 'cityInfoText' => $cityInfoText, 'stadt' => $stadt, 'url' => $url));
    }


    /**
     * @Route("/{slug}/closed",name="workflow_closed",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function closedAction(Request $request, Stadt $stadt)
    {

        return $this->render('workflow/closed.html.twig', array('stadt' => $stadt));
    }


    /**
     * @Route("/confirmEmail",name="workflow_confirm_Email",methods={"GET","POST"})
     */
    public function confirmAction(Request $request, MailerService $mailer, TranslatorInterface $translator,ConfirmEmailService $confirmEmailService)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('uid')));
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt'));

        $res = $confirmEmailService->confirm($stammdaten,$stadt,$request->get('redirect'),$request);
        if ($res === null ){
            return new RedirectResponse( $request->get('redirect'));
        }
        return new Response($res);
    }


    /**
     * @Route("/resetMail",name="workflow_reset_Email",methods={"GET","POST"})
     */
    public function resetAction(Request $request, MailerService $mailer, TranslatorInterface $translator)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('uid')));
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt'));
        $text = $translator->trans('Die Email konnte nicht erneut vesandt werden');
        if ($request->get('resendEmail') == $stammdaten->getResendEmail()) {
            $stammdaten->setConfirmEmailSend(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($stammdaten);
            $em->flush();
            $text = $translator->trans('Die Email wurde erfolgreich versandt');
        }

        return $this->redirectToRoute('workflow_confirm_Email', array('stadt' => $stadt->getId(), 'snack' => $text, 'uid' => $stammdaten->getUid(), 'redirect' => $request->get('redirect')));
    }

    /**
     * @Route("/{slug}/{org_id}/datenschutz",name="workflow_datenschutz",methods={"GET"})
     */
    public function datenschutzAction(Request $request, TranslatorInterface $translator)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $org_datenschutz = $organisation->translate()->getDatenschutz();
        return $this->render('workflow/datenschutz.html.twig', array('datenschutz' => $org_datenschutz, 'org' => $organisation,'stadt' => $organisation->getStadt(),  'redirect' => $request->get('redirect')));
    }

    /**
     * @Route("/{slug}/{org_id}/datenschutz/pdf",name="workflow_datenschutz_pdf",methods={"GET"})
     */
    public function datenschutzpdf(Request $request, TranslatorInterface $translator, PrintAGBService $printAGBService)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        return $printAGBService->printAGB($organisation->translate()->getDatenschutz(), 'D', null, $organisation);

    }

    /**
     * @Route("/{slug}/agb",name="workflow_agb",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function agbAction(Request $request, TranslatorInterface $translator, Stadt $stadt)
    {
        $stadtAGB = $stadt->translate()->getAgb();
        return $this->render('workflow/agb.html.twig', array('stadtAGB' => $stadtAGB, 'stadt' => $stadt,'redirect' => $request->get('redirect')));
    }


    /**
     * @Route("/{slug}/agb/pdf",name="workflow_agb_pdf",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function pdf(Request $request, TranslatorInterface $translator, PrintAGBService $printAGBService, Stadt $stadt)
    {
        return $printAGBService->printAGB($stadt->translate()->getAgb(), 'D', $stadt, null);

    }
}
