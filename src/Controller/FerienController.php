<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachKind;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien", name="ferien")
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function ferienAction(Request $request, Stadt $stadt)
    {
        // Load all schools from the city into the controller as $schulen
        $org = $this->getDoctrine()->getRepository(Organisation::class)->findBy(array('stadt' => $stadt, 'deleted' => false));

        // load parent address data into controller as $adresse
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }
        $kinder = array();
        if ($request->cookies->get('KindID')) {
            $cookie_kind = explode('.', $request->cookies->get('KindID'));
            $kinder = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('id' => $cookie_kind[0]));
        } else {
            $kinder = $adresse->getKinds()->toArray();
        }
        $renderKinder = array();
        foreach ($kinder as $data) {
            $renderKinder[$data->getSchule()->getId()] = $data;
        }
        return $this->render('ferien/ferien.html.twig', array('org' => $org, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $renderKinder));

    }

    /**
     * @Route("/{slug}/ferien/kind/neu",name="ferien_kind_neu",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function ferienNeukindAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $kind = new Kind();
        $kind->setEltern($adresse);
        $kind->setSchule(null);
        $form = $this->createForm(LoerrachKind::class, $kind, array('action' => $this->generateUrl('ferien_kind_neu', array('slug'=>$stadt->getSlug()))));

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) == 0) {

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generateUrl('ferien_kind_zeitblock', array('slug' => $stadt->getSlug()))));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('stadt' => $stadt, 'form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/ferien/kind/zeitblock",name="ferien_kind_zeitblock",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function zeitblockAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt)
    {

        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $schule = $kind->getSchule();

        $schuljahr = $this->getSchuljahr($stadt);
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        );
        $block = array();
        if ($kind->getArt() == 1) {
            $req['ganztag'] = 0;
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));
            $req['ganztag'] = $kind->getArt();
            $block = array_merge($block, $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc')));

        } elseif ($kind->getArt() == 2) {
            $req['ganztag'] = $kind->getArt();
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        }

        $renderBlocks = array();
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->render('ferien/blocks.html.twig', array('kind' => $kind, 'blocks' => $renderBlocks));
    }

    /**
     * @Route("/loerrach/kinder/block/toggle",name="ferien_kinder_block_toggle",methods={"GET"})
     */
    public function kinderblocktoggleAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $result = array(
            'text' => $translator->trans('Betreuungsblock erfolgreich gespeichert'),
            'error' => 0,
            'kontingent' => false,
            'cardText' => $translator->trans('Gebucht')
        );
        try {
            //Include Parents in this route
            $adresse = new Stammdaten;
            if ($this->getStammdatenFromCookie($request)) {
                $adresse = $this->getStammdatenFromCookie($request);
            }

            $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kinder_id')));
            $result['preisUrl'] = $this->generateUrl('loerrach_workflow_preis_einKind', array('kind_id' => $kind->getId()));
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block_id'));
            if ($block->getMin() || $block->getMax()) {
                $result['kontingent'] = true;
                $result['cardText'] = $translator->trans('Angemeldet');
            }
            if ($block->getMin() || $block->getMax()) {
                if (in_array($block, $kind->getBeworben()->toArray())) {
                    $kind->removeBeworben($block);
                } else {
                    $kind->addBeworben($block);
                }
            } else {
                if (in_array($block, $kind->getZeitblocks()->toArray())) {
                    $kind->removeZeitblock($block);
                } else {
                    $kind->addZeitblock($block);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($kind);
            $em->flush();

            $blocks2 = $kind->getTageWithBlocks();

            if ($blocks2 < 2) {
                $result['text'] = $translator->trans('Bitte weiteren Betreuungsblock auswählen (Mindestens zwei Tage müssen ausgewählt werden)');
                $result['error'] = 2;
            }
        } catch (\Exception $e) {
            $result['text'] = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        return new JsonResponse($result);
    }

    // Nach UId und Fin fragen
    private function getStammdatenFromCookie(Request $request)
    {
        if ($request->cookies->get('UserID')) {


            $cookie_ar = explode('.', $request->cookies->get('UserID'));
            $hash = hash("sha256", $cookie_ar[0] . $this->getParameter("secret"));
            $search = array('uid' => $cookie_ar[0], 'saved' => false);
            if ($request->cookies->get('KindID') && $request->cookies->get('SecID')) {


                $cookie_kind = explode('.', $request->cookies->get('KindID'));
                $hash_kind = hash("sha256", $cookie_kind[0] . $this->getParameter("secret"));

                $cookie_seccode = explode('.', $request->cookies->get('SecID'));
                $hash_seccode = hash("sha256", $cookie_seccode[0] . $this->getParameter("secret"));


            } else {
                $search['history'] = 0;
            }

            if ($hash == $cookie_ar[1]) {
                $adresse = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy($search);
                return $adresse;
            }

            return null;
        }
        return null;
    }
}
