<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Tags;
use App\Form\Type\ElternFerien;
use App\Form\Type\FerienKind;
use App\Repository\StadtRepository;
use App\Service\FerienAbschluss;
use App\Service\StamdatenFromCookie;
use App\Service\ToogleKindFerienblock;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienController extends AbstractController
{
    public const BEZEICHNERCOOKIE = 'FerienUserID';
    public const BEZEICHNERCOOKIEKINDER = 'FerienKinderID';

    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/{slug}/ferien/adresse', name: 'ferien_adresse', methods: ['GET', 'POST'])]
    public function adresseAction(TranslatorInterface $translator, Request $request, ValidatorInterface $validator, $slug, StamdatenFromCookie $stamdatenFromCookie)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);

        if ($stadt === null) {
            return $this->redirectToRoute('workflow_city_not_found');
        }
        // load parent address data into controller as $adresse
        $adresse = new Stammdaten();
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }
        if ($adresse->getFin() === true) {
            return $this->redirectToRoute('ferien_bezahlung_prepare', ['slug' => $stadt->getSlug()]);
        }
        // Add SecCode into if to create a SecCode the first time to be not "null"
        if ($adresse->getUid() === null) {
            $adresse->setUid(md5(uniqid()))
                ->setAngemeldet(false);
        }

        // Check if admin has enabled ferienprogramm for the city
        if ($stadt->getFerienprogramm() === false) {
            return $this->redirect($this->generateUrl('workflow_start', ['slug' => $stadt->getSlug()]));
        }

        $form = $this->createForm(ElternFerien::class, $adresse, ['stadt' => $stadt]);

        $form->handleRequest($request);

        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if (count($errors) == 0) {
                $adresse->setFin(false);
                $cookie = new Cookie(self::BEZEICHNERCOOKIE, $adresse->getUid() . '.' . hash('sha256', $adresse->getUid() . $this->getParameter('secret')), time() + 60 * 60 * 24 * 365);
                $em = $this->managerRegistry->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('workflow_confirm_Email', ['redirect' => $this->generateUrl('ferien_auswahl', ['slug' => $stadt->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL), 'uid' => $adresse->getUid(), 'stadt' => $stadt->getId()]);
                $response->headers->setCookie($cookie);

                return $response;
            }
        }
        $title = $translator->trans('Online Anmeldung für Ferienbetreuung') . '->' . $translator->trans('Adresse') . ' | ' . $stadt->getName();

        return $this->render('ferien/adresse.html.twig', ['title' => $title, 'stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors]);
    }

    #[Route(path: '/{slug}/ferien/auswahl', name: 'ferien_auswahl', methods: ['GET'])]
    #[ParamConverter('stadt', options: ['mapping' => ['slug' => 'slug']])]
    public function ferienAction(Request $request, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Load all schools from the city into the controller as $schulen
        $org = $this->managerRegistry->getRepository(Organisation::class)->findBy(['stadt' => $stadt, 'deleted' => false]);

        // Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        } else {
            return $this->redirect($this->generateUrl('ferien_adresse', ['slug' => $stadt->getSlug()]));
        }
        if ($adresse->getFin() === true) {
            return $this->redirectToRoute('ferien_bezahlung_prepare', ['slug' => $stadt->getSlug()]);
        }

        $kinder = [];
        if ($request->cookies->get(self::BEZEICHNERCOOKIEKINDER)) {
            $cookie_kind = explode('.', $request->cookies->get(self::BEZEICHNERCOOKIEKINDER));
            $kinder = $this->managerRegistry->getRepository(Kind::class)->findBy(['id' => $cookie_kind[0]]);
        } else {
            $kinder = $adresse->getKinds()->toArray();
        }

        return $this->render('ferien/ferien.html.twig', ['org' => $org, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $kinder]);
    }

    #[Route(path: '/{slug}/ferien/kind/neu', name: 'ferien_kind_neu', methods: ['GET', 'POST'])]
    #[ParamConverter('stadt', options: ['mapping' => ['slug' => 'slug']])]
    public function ferienNeukindAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, StadtRepository $stadtRepository)
    {
        // Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }

        $kind = new Kind();
        $kind->setEltern($adresse);
        $kind->setSchule(null);
        $form = $this->createForm(FerienKind::class, $kind, ['stadt' => $stadt, 'action' => $this->generateUrl('ferien_kind_neu', ['slug' => $stadt->getSlug()])]);
        $form->remove('klasse');
        $form->remove('art');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) === 0) {
                    $em = $this->managerRegistry->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');

                    return new JsonResponse(['error' => 0, 'snack' => $text, 'next' => $this->generateUrl('ferien_kind_programm', ['slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()])]);
                }
            } catch (\Exception) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');

                return new JsonResponse(['error' => 1, 'snack' => $text]);
            }
        }

        return $this->render('ferien/kindForm.html.twig', ['stadt' => $stadt, 'form' => $form->createView()]);
    }

    #[Route(path: '/{slug}/ferien/kind/edit', name: 'ferien_workflow_kind_edit', methods: ['GET', 'POST'])]
    public function kindEditAction($slug, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie, TranslatorInterface $translator)
    {
        // Include Parents in this route
        $adresse = new Stammdaten();
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }

        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(['eltern' => $adresse, 'id' => $request->get('kind_id')]);

        $form = $this->createForm(FerienKind::class, $kind, ['stadt' => $stadt, 'action' => $this->generateUrl('ferien_workflow_kind_edit', ['slug' => $slug, 'kind_id' => $kind->getId()])]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) === 0) {
                    $em = $this->managerRegistry->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');

                    return new JsonResponse(['error' => 0, 'snack' => $text]);
                }
            } catch (\Exception) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');

                return new JsonResponse(['error' => 1, 'snack' => $text]);
            }
        }

        return $this->render('ferien/kindForm.html.twig', ['stadt' => $stadt, 'form' => $form->createView()]);
    }

    #[Route(path: '/{slug}/ferien/kind/delete', name: 'ferien_workflow_kind_delete', methods: ['DELETE'])]
    public function deleteAction($slug, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Include Parents in this route
        $adresse = new Stammdaten();
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(['eltern' => $adresse, 'id' => $request->get('kind_id')]);
        $em = $this->managerRegistry->getManager();
        $em->remove($kind);
        $em->flush();

        return new JsonResponse(['redirect' => $this->generateUrl('ferien_auswahl', ['slug' => $slug])]);
    }

    #[Route(path: '/{slug}/ferien/programm', name: 'ferien_kind_programm', methods: ['GET', 'POST'])]
    #[ParamConverter('stadt', options: ['mapping' => ['slug' => 'slug']])]
    public function programAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }
        $param = json_decode((string) $request->get('param'));

        $startDate = null;
        $endDate = null;
        $tag = [];
        $onlyEmptyCourse = null;
        if ($param) {
            $startDate = isset($param->start) ? new \DateTime($param->start) : null;
            $endDate = isset($param->end) ? new \DateTime($param->end) : null;
            $onlyEmptyCourse = $param->freeSpace ?? null;
            foreach ($param->tag as $data) {
                $tag[] = $this->managerRegistry->getRepository(Tags::class)->find($data);
            }
        }

        $tags = $this->managerRegistry->getRepository(Tags::class)->findAll();
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(['eltern' => $adresse, 'id' => $request->get('kind_id')]);
        $dates = $this->managerRegistry->getRepository(Ferienblock::class)->findFerienblocksFromToday($stadt, $startDate, $endDate, $tag, $onlyEmptyCourse);
        $today = new \DateTime('today');

        return $this->render('ferien/blocks.html.twig', ['kind' => $kind, 'dates' => $dates, 'stadt' => $stadt, 'today' => $today, 'tags' => $tags]);
    }

    #[Route(path: '/{slug}/ferien/programm/toggle', name: 'ferien_kinder_block_toggle', methods: ['PATCH'])]
    #[ParamConverter('stadt', options: ['mapping' => ['slug' => 'slug']])]
    public function ferienblocktoggleAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, ToogleKindFerienblock $toogleKindFerienblock, StamdatenFromCookie $stamdatenFromCookie)
    {
        $adresse = new Stammdaten();
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        }

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(['eltern' => $adresse, 'id' => $request->get('kind_id')]);
        $block = $this->managerRegistry->getRepository(Ferienblock::class)->find($request->get('block_id'));
        $result = $toogleKindFerienblock->toggleKind($kind, $block, $request->get('preis_id'));

        return new JsonResponse($result);
    }

    #[Route(path: '/{slug}/ferien/zusammenfassung', name: 'ferien_zusammenfassung', methods: ['Get', 'POST'])]
    #[ParamConverter('stadt', options: ['mapping' => ['slug' => 'slug']])]
    public function zusammenfassungAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        try {
            // Include Parents in this route
            if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
                $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
            }
            if ($adresse->getFin() === true) {
                return $this->redirectToRoute('ferien_bezahlung_prepare', ['slug' => $stadt->getSlug()]);
            }
            $kind = $adresse->getKinds();
        } catch (\Exception) {
        }

        return $this->render('ferien/zusammenfassung.html.twig', ['kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt, 'error' => true]);
    }

    #[Route(path: '/{slug}/ferien/abschluss', name: 'ferien_abschluss', methods: ['Get', 'POST'])]
    public function abschlussAction(TranslatorInterface $translator, FerienAbschluss $ferienAbschluss, $slug, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, self::BEZEICHNERCOOKIE);
        } else {
            return $this->redirectToRoute('ferien_adresse', ['slug' => $slug]);
        }
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);
        // überprüfe ob alle Payment vorhanden sind
        $check = $ferienAbschluss->checkIfStillSpace($adresse);
        if ($check !== null) {
            return $this->redirectToRoute('ferien_auswahl', ['slug' => $stadt->getSlug(), 'snack' => $translator->trans('Das Ferienprogramm %kursname% ist bereits ausgebucht oder Sie haben zu viele Kinder angemeldet', ['%kursname%' => $check->translate()->getTitel()])]);
        }
        $res = $ferienAbschluss->startAbschluss($adresse, $stadt);

        if ($res === true) {
            $result = $this->render('ferien/abschluss.html.twig', ['stadt' => $stadt]);
            $result->headers->clearCookie(self::BEZEICHNERCOOKIE);

            return $result;
        }

        return $this->redirectToRoute('ferien_bezahlung_prepare', ['slug' => $stadt->getSlug()]);
    }
}
