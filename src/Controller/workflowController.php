<?php

namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Active;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\ConfirmEmailService;
use App\Service\MailerService;
use App\Service\PrintAGBService;
use App\Service\PrintDatenschutzService;
use App\Service\SchuljahrService;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class workflowController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/{slug}/home",name="workflow_start",methods={"GET"})
     */
    public function welcomeAction(TranslatorInterface $translator, Request $request, $slug, SchuljahrService $schuljahrService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));

        if ($stadt === null) {
            return $this->redirectToRoute('workflow_city_not_found');
        }

        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $activeSchuljahr = $this->managerRegistry->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $cityInfoText = $stadt->translate()->getInfoText();
        // Load all schools from the city into the controller as $schulen
        $schule = $this->managerRegistry->getRepository(Schule::class)->findBy(array('stadt' => $stadt, 'deleted' => false));
        $title = $translator->trans('Anmeldeportal') . ' ' . $stadt->getName();
        if ($stadt->getSchulkindBetreung() && $stadt->getFerienprogramm()) {
            $title = $translator->trans('Schulkindbetreuung und Ferienbetreuung der ') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        } elseif ($stadt->getSchulkindBetreung()) {
            $title = $translator->trans('Anmeldeportal Schulkindbetreuung') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        } elseif ($stadt->getFerienprogramm()) {
            $title = $translator->trans('Ferienprogramm buchen') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        }
        $news = $this->managerRegistry->getRepository(News::class)->findBy(array('stadt' => $stadt, 'activ' => true), array('date' => 'DESC'));
        $text = $stadt->translate()->getInfoText();
        $array = explode('. ', $text);
        $metaDescription = $this->buildMeta($array);

        return $this->render('workflow/start.html.twig', array('metaDescription' => $metaDescription, 'title' => $title, 'schule' => $schule, 'news' => $news, 'cityInfoText' => $cityInfoText, 'stadt' => $stadt, 'schuljahr' => $schuljahr, 'activeSchuljahr' => $activeSchuljahr));
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
     * @Route("/city-not-found",name="workflow_city_not_found",methods={"GET"})
     */
    public function noCityAction(Request $request)
    {

        return $this->render('workflow/noCity.html.twig');
    }

    /**
     * @Route("/wartung",name="workflow_wartung",methods={"GET"})
     */
    public function wartungAction(Request $request)
    {
        return $this->render('workflow/wartung.html.twig', array('referer' => $request->get('redirect')));
    }

    /**
     * @Route("/confirmEmail",name="workflow_confirm_Email",methods={"GET","POST"})
     */
    public function confirmAction(Request $request, MailerService $mailer, TranslatorInterface $translator, ConfirmEmailService $confirmEmailService)
    {
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('uid')));
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt'));

        $res = $confirmEmailService->confirm($stammdaten, $stadt, $request->get('redirect'), $request);
        $url = parse_url($request->get('redirect'))['host'];
        if ($res === null) {
            if ($url == $request->getHost()) {
                return new RedirectResponse($request->get('redirect'));
            } else {
                throw new \Exception('Wrong Redirect Adress');
            }


        }
        return new Response($res);
    }


    /**
     * @Route("/resetMail",name="workflow_reset_Email",methods={"GET","POST"})
     */
    public function resetAction(Request $request, MailerService $mailer, TranslatorInterface $translator)
    {
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('uid')));
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt'));
        $text = $translator->trans('Die E-Mail konnte nicht erneut vesandt werden');
        if ($request->get('resendEmail') == $stammdaten->getResendEmail()) {
            $stammdaten->setConfirmEmailSend(false);
            $em = $this->managerRegistry->getManager();
            $em->persist($stammdaten);
            $em->flush();
            $text = $translator->trans('Die E-Mail wurde erfolgreich versandt');
        }

        return $this->redirectToRoute('workflow_confirm_Email', array('stadt' => $stadt->getId(), 'snack' => $text, 'uid' => $stammdaten->getUid(), 'redirect' => $request->get('redirect')));
    }

    /**
     * @Route("/{slug}/{org_id}/datenschutz",name="workflow_datenschutz",methods={"GET"})
     */
    public function datenschutzAction($slug, $org_id, Request $request, TranslatorInterface $translator)
    {

        if ($org_id == 'city') {

            $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
            $org_datenschutz = $stadt->translate()->getDatenschutz();
            $titel = $translator->trans('Datenschutzhinweis %organisation%', array('%organisation%' => $stadt->getName())) . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
            $metaDescrition = $translator->trans('Datenschutzhinweis %organisation%', array('%organisation%' => $stadt->getName()));

            return $this->render('workflow/datenschutz.html.twig', array('metaDescription' => $metaDescrition, 'title' => $titel, 'datenschutz' => $org_datenschutz, 'org' => $stadt, 'org_id' => $org_id, 'stadt' => $stadt));
        } else {
            $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
            $org_datenschutz = $organisation->translate()->getDatenschutz();
            $stadt = $organisation->getStadt();
            $titel = $translator->trans('Datenschutzhinweis %organisation%', array('%organisation%' => $organisation->getName())) . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
            $metaDescrition = $translator->trans('Datenschutzhinweis %organisation%', array('%organisation%' => $organisation->getName()));

            return $this->render('workflow/datenschutz.html.twig', array('metaDescription' => $metaDescrition, 'title' => $titel, 'datenschutz' => $org_datenschutz, 'org' => $organisation, 'org_id' => $org_id, 'stadt' => $stadt));
        }
    }


    /**
     * @Route("/{slug}/{org_id}/datenschutz/pdf",name="workflow_datenschutz_pdf",methods={"GET"})
     */
    public function datenschutzpdf(Request $request, TranslatorInterface $translator, PrintDatenschutzService $printDatenschutzService, $slug, $org_id)
    {
        if ($org_id == 'city') {

            $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
            return $printDatenschutzService->printDatenschutz($stadt->translate()->getDatenschutz(), 'D', $stadt, null);
        } else {
            $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
            $stadt = $organisation->getStadt();
            return $printDatenschutzService->printDatenschutz($organisation->translate()->getDatenschutz(), 'D', null, $organisation);
        }



    }

    /**
     * @Route("/{slug}/vertragsbedingungen",name="workflow_agb",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function agbAction(Request $request, TranslatorInterface $translator, Stadt $stadt)
    {
        $stadtAGB = $stadt->translate()->getAgb();
        $titel = $translator->trans('Vertragsbedingungen') . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
        $metaDescrition = $translator->trans('Allgemeine Vertragsbedingungen der %stadt%', array('%stadt%' => $stadt->getName()));

        return $this->render('workflow/agb.html.twig', array('metaDescription' => $metaDescrition, 'title' => $titel, 'stadtAGB' => $stadtAGB, 'stadt' => $stadt, 'redirect' => $request->get('redirect')));
    }


    /**
     * @Route("/{slug}/agb/pdf",name="workflow_agb_pdf",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function pdf(Request $request, TranslatorInterface $translator, PrintAGBService $printAGBService, Stadt $stadt)
    {
        return $printAGBService->printAGB($stadt->translate()->getAgb(), 'D', $stadt, null);

    }


    /**
     * @Route("/{slug}/imprint",name="workflow_imprint",methods={"GET"})
     */
    public function imprintAction($slug, Request $request, TranslatorInterface $translator)
    {
        if ($slug === null) {
            return $this->redirectToRoute('impressum');
        }
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $titel = $translator->trans('Impressum') . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
        $metaDescrition = $translator->trans('Impressum %stadt%', array('%stadt%' => $stadt->getName()));
        if ($stadt->getImprint() !== null) {
            return $this->render('workflow/imprint.html.twig', array('metaDescription' => $metaDescrition, 'title' => $titel, 'stadt' => $stadt));
        } else {
            return $this->redirectToRoute('impressum');
        }

    }

    private function buildMeta($sentenceArray)
    {
        $count = 0;
        $res = '';
        foreach ($sentenceArray as $data) {

            if ($count <= 160) {
                $res .= $data . '. ';
            } else {
                break;
            }
            $count += strlen($data);
        }

        return $res;
    }
}
