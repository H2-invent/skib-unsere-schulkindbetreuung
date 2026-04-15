<?php

namespace App\Controller;

/*
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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class workflowController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/{slug}/home', name: 'workflow_start', methods: ['GET'])]
    public function welcomeAction(TranslatorInterface $translator, Request $request, $slug, SchuljahrService $schuljahrService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);

        if ($stadt === null) {
            return $this->redirectToRoute('workflow_city_not_found');
        }

        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $anmeldeSchuljahr = $this->managerRegistry->getRepository(Active::class)->findAnmeldeSchuljahrFromCity($stadt);
        $aktiveSchuljahre = $this->managerRegistry->getRepository(Active::class)->findAllActualSchuljahrFromCity(stadt: $stadt, today: new \DateTime());
        $schuljahre = [];
        foreach ($aktiveSchuljahre as $data) {
            if ($data !== $anmeldeSchuljahr) {
                $schuljahre[] = $data;
            }
        }
        $cityInfoText = $stadt->translate()->getInfoText();
        // Load all schools from the city into the controller as $schulen
        $schule = $this->managerRegistry->getRepository(Schule::class)->findBy(['stadt' => $stadt, 'deleted' => false], ['name' => 'DESC']);
        $title = $translator->trans('Anmeldeportal') . ' ' . $stadt->getName();
        if ($stadt->getSchulkindBetreung() && $stadt->getFerienprogramm()) {
            $title = $translator->trans('Schulkindbetreuung und Ferienbetreuung der ') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        } elseif ($stadt->getSchulkindBetreung()) {
            $title = $translator->trans('Anmeldeportal Schulkindbetreuung') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        } elseif ($stadt->getFerienprogramm()) {
            $title = $translator->trans('Ferienprogramm buchen') . ' ' . $stadt->getName() . ' | ' . $translator->trans(' Hier anmelden');
        }
        $news = $this->managerRegistry->getRepository(News::class)->findBy(['stadt' => $stadt, 'activ' => true], ['date' => 'DESC']);
        $text = $stadt->translate()->getInfoText();
        $array = explode('. ', (string) $text);
        $metaDescription = $this->buildMeta($array);

        return $this->render('workflow/start.html.twig', ['metaDescription' => $metaDescription, 'title' => $title, 'schule' => $schule, 'news' => $news, 'cityInfoText' => $cityInfoText, 'stadt' => $stadt, 'schuljahr' => $schuljahr, 'activeSchuljahr' => $schuljahre]);
    }

    #[Route(path: '/{slug}/closed', name: 'workflow_closed', methods: ['GET'])]
    public function closedAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt)
    {
        return $this->render('workflow/closed.html.twig', ['stadt' => $stadt]);
    }

    #[Route(path: '/city-not-found', name: 'workflow_city_not_found', methods: ['GET'])]
    public function noCityAction(Request $request)
    {
        return $this->render('workflow/noCity.html.twig');
    }

    #[Route(path: '/wartung', name: 'workflow_wartung', methods: ['GET'])]
    public function wartungAction(Request $request)
    {
        return $this->render('workflow/wartung.html.twig', ['referer' => $request->get('redirect')]);
    }

    #[Route(path: '/confirmEmail', name: 'workflow_confirm_Email', methods: ['GET', 'POST'])]
    public function confirmAction(Request $request, MailerService $mailer, TranslatorInterface $translator, ConfirmEmailService $confirmEmailService)
    {
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(['uid' => $request->get('uid')]);
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt'));

        $res = $confirmEmailService->confirm($stammdaten, $stadt, $request->get('redirect'), $request);
        $url = parse_url((string) $request->get('redirect'))['host'];
        if ($res === null) {
            if ($url == $request->getHost()) {
                return new RedirectResponse($request->get('redirect'));
            }
            throw new \Exception('Wrong Redirect Adress');
        }

        return new Response($res);
    }

    #[Route(path: '/resetMail', name: 'workflow_reset_Email', methods: ['GET', 'POST'])]
    public function resetAction(Request $request, MailerService $mailer, TranslatorInterface $translator)
    {
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(['uid' => $request->get('uid')]);
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt'));
        $text = $translator->trans('Die E-Mail konnte nicht erneut vesandt werden');
        if ($request->get('resendEmail') == $stammdaten->getResendEmail()) {
            $stammdaten->setConfirmEmailSend(false);
            $em = $this->managerRegistry->getManager();
            $em->persist($stammdaten);
            $em->flush();
            $text = $translator->trans('Die E-Mail wurde erfolgreich versandt');
        }

        return $this->redirectToRoute('workflow_confirm_Email', ['stadt' => $stadt->getId(), 'snack' => $text, 'uid' => $stammdaten->getUid(), 'redirect' => $request->get('redirect')]);
    }

    #[Route(path: '/{slug}/{org_id}/datenschutz', name: 'workflow_datenschutz', methods: ['GET'])]
    public function datenschutzAction($slug, $org_id, Request $request, TranslatorInterface $translator)
    {
        if ($org_id == 'city') {
            $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);
            $org_datenschutz = $stadt->translate()->getDatenschutz();
            $titel = $translator->trans('Datenschutzhinweis %organisation%', ['%organisation%' => $stadt->getName()]) . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
            $metaDescrition = $translator->trans('Datenschutzhinweis %organisation%', ['%organisation%' => $stadt->getName()]);

            return $this->render('workflow/datenschutz.html.twig', ['metaDescription' => $metaDescrition, 'title' => $titel, 'datenschutz' => $org_datenschutz, 'org' => $stadt, 'org_id' => $org_id, 'stadt' => $stadt]);
        }
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $org_datenschutz = $organisation->translate()->getDatenschutz();
        $stadt = $organisation->getStadt();
        $titel = $translator->trans('Datenschutzhinweis %organisation%', ['%organisation%' => $organisation->getName()]) . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
        $metaDescrition = $translator->trans('Datenschutzhinweis %organisation%', ['%organisation%' => $organisation->getName()]);

        return $this->render('workflow/datenschutz.html.twig', ['metaDescription' => $metaDescrition, 'title' => $titel, 'datenschutz' => $org_datenschutz, 'org' => $organisation, 'org_id' => $org_id, 'stadt' => $stadt]);
    }

    #[Route(path: '/{slug}/{org_id}/datenschutz/pdf', name: 'workflow_datenschutz_pdf', methods: ['GET'])]
    public function datenschutzpdf(Request $request, TranslatorInterface $translator, PrintDatenschutzService $printDatenschutzService, $slug, $org_id)
    {
        if ($org_id == 'city') {
            $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);

            return $printDatenschutzService->printDatenschutz($stadt->translate()->getDatenschutz(), 'D', $stadt, null);
        }
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $stadt = $organisation->getStadt();

        return $printDatenschutzService->printDatenschutz($organisation->translate()->getDatenschutz(), 'D', null, $organisation);
    }

    #[Route(path: '/{slug}/vertragsbedingungen', name: 'workflow_agb', methods: ['GET'])]
    public function agbAction(Request $request, TranslatorInterface $translator, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt)
    {
        $stadtAGB = $stadt->translate()->getAgb();
        $titel = $translator->trans('Vertragsbedingungen') . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
        $metaDescrition = $translator->trans('Allgemeine Vertragsbedingungen der %stadt%', ['%stadt%' => $stadt->getName()]);

        return $this->render('workflow/agb.html.twig', ['metaDescription' => $metaDescrition, 'title' => $titel, 'stadtAGB' => $stadtAGB, 'stadt' => $stadt, 'redirect' => $request->get('redirect')]);
    }

    #[Route(path: '/{slug}/agb/pdf', name: 'workflow_agb_pdf', methods: ['GET'])]
    public function pdf(Request $request, TranslatorInterface $translator, PrintAGBService $printAGBService, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt)
    {
        return $printAGBService->printAGB($stadt->translate()->getAgb(), 'D', $stadt, null);
    }

    #[Route(path: '/{slug}/imprint', name: 'workflow_imprint', methods: ['GET'])]
    public function imprintAction($slug, Request $request, TranslatorInterface $translator)
    {
        if ($slug === null) {
            return $this->redirectToRoute('impressum');
        }
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);
        $titel = $translator->trans('Impressum') . ' | ' . $stadt->getName() . ' | unsere-Schulkindbetreuung.de';
        $metaDescrition = $translator->trans('Impressum %stadt%', ['%stadt%' => $stadt->getName()]);
        if ($stadt->getImprint() !== null) {
            return $this->render('workflow/imprint.html.twig', ['metaDescription' => $metaDescrition, 'title' => $titel, 'stadt' => $stadt]);
        }

        return $this->redirectToRoute('impressum');
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
            $count += strlen((string) $data);
        }

        return $res;
    }
}
