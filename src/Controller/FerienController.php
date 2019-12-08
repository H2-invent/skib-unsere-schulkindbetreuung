<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\LoerrachEltern;
use App\Form\Type\LoerrachKind;
use App\Form\Type\PaymentType;
use App\Service\StamdatenFromCookie;
use App\Service\ToogleKindFerienblock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Braintree\Gateway;

class FerienController extends AbstractController
{
    private $BEZEICHNERCOOKIE = 'FerienUserID';
    private $BEZEICHNERCOOKIEKINDER = 'FerienKinderID';
    /**
     * @Route("/{slug}/ferien/adresse",name="ferien_adresse",methods={"GET","POST"})
     */
    public function adresseAction(Request $request, ValidatorInterface $validator, $slug, StamdatenFromCookie $stamdatenFromCookie)
    {

        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));

        if ($stadt === null) {
            return $this->redirectToRoute('workflow_city_not_found');
        }
        // load parent address data into controller as $adresse
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        //Add SecCode into if to create a SecCode the first time to be not "null"
        if ($adresse->getUid() === null) {
            $adresse->setUid(md5(uniqid()))
                ->setAngemeldet(false);
            $adresse->setCreatedAt(new \DateTime());
        }

        //Check if admin has enabled ferienprogramm for the city
        if ($stadt->getFerienprogramm() === false) {
            return $this->redirect($this->generateUrl('workflow_start', array('slug' => $stadt->getSlug())));
        }

        $form = $this->createForm(LoerrachEltern::class, $adresse);
        $form->remove('alleinerziehend', 'kinderImKiga', 'beruflicheSituation', 'einkommen');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if (count($errors) == 0) {
                $adresse->setFin(false);
                $cookie = new Cookie ($this->BEZEICHNERCOOKIE, $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('workflow_confirm_Email', array('redirect' => $this->generateUrl('ferien_auswahl', array('slug' => $stadt->getSlug())), 'uid' => $adresse->getUid(), 'stadt' => $stadt->getId()));
                $response->headers->setCookie($cookie);
                return $response;
            }

        }

        return $this->render('ferien/adresse.html.twig', array('stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/{slug}/ferien/auswahl", name="ferien_auswahl", methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function ferienAction(Request $request, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Load all schools from the city into the controller as $schulen
        $org = $this->getDoctrine()->getRepository(Organisation::class)->findBy(array('stadt' => $stadt, 'deleted' => false));

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $kinder = array();
        if ($request->cookies->get($this->BEZEICHNERCOOKIEKINDER)) {
            $cookie_kind = explode('.', $request->cookies->get($this->BEZEICHNERCOOKIEKINDER));
            $kinder = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('id' => $cookie_kind[0]));

        } else {
            $kinder = $adresse->getKinds()->toArray();

        }

        return $this->render('ferien/ferien.html.twig', array('org' => $org, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $kinder));

    }


    /**
     * @Route("/{slug}/ferien/kind/neu",name="ferien_kind_neu",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function ferienNeukindAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $kind = new Kind();
        $kind->setEltern($adresse);
        $kind->setSchule(null);
        $form = $this->createForm(LoerrachKind::class, $kind, array('action' => $this->generateUrl('ferien_kind_neu', array('slug' => $stadt->getSlug()))));
        $form->remove('klasse');
        $form->remove('art');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) === 0) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generateUrl('ferien_kind_programm', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }
        }
        return $this->render('ferien/kindForm.html.twig', array('stadt' => $stadt, 'form' => $form->createView()));
    }


    /**
     * @Route("/{slug}/ferien/kind/edit",name="ferien_workflow_kind_edit",methods={"GET","POST"})
     */
    public function kindEditAction($slug, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie, TranslatorInterface $translator)
    {
        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));

        $form = $this->createForm(LoerrachKind::class, $kind, array('action' => $this->generateUrl('ferien_workflow_kind_edit', array('slug' => $slug,'kind_id' => $kind->getId()))));
        $form->remove('klasse');
        $form->remove('art');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) === 0) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }
        }
        return $this->render('ferien/kindForm.html.twig', array('stadt' => $stadt, 'form' => $form->createView()));
    }


    /**
     * @Route("/{slug}/ferien/kind/delete",name="ferien_workflow_kind_delete",methods={"DELETE"})
     */
    public function deleteAction($slug, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie)
    {
        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $em = $this->getDoctrine()->getManager();
        $em->remove($kind);
        $em->flush();
        return new JsonResponse(array('redirect' => $this->generateUrl('ferien_auswahl',array('slug'=>$slug))));
    }


    /**
     * @Route("/{slug}/ferien/programm",name="ferien_kind_programm",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function programAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $dates = $this->getDoctrine()->getRepository(Ferienblock::class)->findFerienblocksFromToday($stadt);
        $today = new \DateTime('today');

        return $this->render('ferien/blocks.html.twig', array('kind' => $kind, 'dates' => $dates, 'stadt' => $stadt, 'today' => $today));
    }


    /**
     * @Route("/{slug}/ferien/programm/toggle",name="ferien_kinder_block_toggle",methods={"PATCH"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function ferienblocktoggleAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, ToogleKindFerienblock $toogleKindFerienblock,StamdatenFromCookie $stamdatenFromCookie)
    {

        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $block = $this->getDoctrine()->getRepository(Ferienblock::class)->find($request->get('block_id'));
        $result = $toogleKindFerienblock->toggleKind($kind, $block, $request->get('preis_id'));

        return new JsonResponse($result);
    }


    /**
     * @Route("/{slug}/ferien/bezahlung",name="ferien_bezahlung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
        }
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
        }

        $gateway =  new Gateway([
            'environment' => 'sandbox',
            'merchantId' => '65xmpcc6hh6khg5d',
            'publicKey' => 'wzkfsj9n2kbyytfp',
            'privateKey' => 'a153a39aaef70466e97773a120b95f91',
        ]);
        $clientToken = $gateway->clientToken()->generate();
        return $this->render('ferien/bezahlung.html.twig', array('stadt' => $stadt,'token'=>$clientToken,'form'=>$form->createView()));
    }


    /**
     * @Route("/{slug}/ferien/zusammenfassung",name="ferien_zusammenfassung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function zusammenfassungAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        try {
            //Include Parents in this route
            if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
                $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
            }

            $kind = $adresse->getKinds();

        } catch (\Exception $e) {
            $result['text'] = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
        }

        return $this->render('ferien/zusammenfassung.html.twig', array('kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt, 'error' => true));
    }


    /**
     * @Route("/{slug}/ferien/abschluss",name="ferien_abschluss",methods={"Get","POST"})
     */
    public function abschlussAction($slug,Request $request, ValidatorInterface $validator, TranslatorInterface $translator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
         //Include Parents in this route
            if ($stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE)) {
                $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request,$this->BEZEICHNERCOOKIE);
            }else {
                return $this->redirectToRoute('ferien_adresse', array('slug'=>$slug));
            }
            $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));

        return $this->render('ferien/abschluss.html.twig', array('stadt' => $stadt));
    }


}
