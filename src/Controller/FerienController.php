<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachKind;
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

class FerienController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/adresse",name="ferien_adresse",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function adresseAction(Request $request, ValidatorInterface $validator, Stadt $stadt)
    {
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        //Add SecCode into if to create a SecCode the first time to be not "null"
        if ($adresse->getUid() == null) {
            $adresse->setUid(md5(uniqid()))
                ->setAngemeldet(false);
            $adresse->setCreatedAt(new \DateTime());
        }

        $form = $this->createFormBuilder($adresse)
            ->add('email', EmailType::class, ['label' => 'Email', 'translation_domain' => 'form'])
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'translation_domain' => 'form', 'help' => 'Das ist eine Hilfe für diese Frage im Form'])
            ->add('name', TextType::class, ['label' => 'Nachname', 'translation_domain' => 'form'])
            ->add('strasse', TextType::class, ['label' => 'Straße', 'translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class, ['required' => false, 'label' => 'Adresszusatz', 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'translation_domain' => 'form'])
            ->add('stadt', TextType::class, ['label' => 'Stadt', 'translation_domain' => 'form', 'help' => 'Das ist eine Hilfe für diese Frage im Form'])
            ->add('notfallName', TextType::class, ['required' => true, 'label' => 'Name und Beziehung des Notfallkontakt', 'translation_domain' => 'form'])
            ->add('notfallkontakt', TextType::class, ['required' => true, 'label' => 'Notfalltelefonnummer', 'translation_domain' => 'form'])
            ->add('iban', TextType::class, ['required' => true, 'label' => 'IBAN für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('bic', TextType::class, ['required' => true, 'label' => 'BIC für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('kontoinhaber', TextType::class, ['required' => true, 'label' => 'Kontoinhaber für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('abholberechtigter', TextareaType::class, ['required' => false, 'label' => 'Weitere abholberechtigte Personen', 'translation_domain' => 'form', 'attr' => ['rows' => 6]])
            ->add('sepaInfo', CheckboxType::class, ['required' => true, 'label' => 'SEPA-LAstschrift Mandat wird elektromisch erteilt', 'translation_domain' => 'form'])
            ->add('gdpr', CheckboxType::class, ['required' => true, 'label' => 'Ich nehme zur Kenntniss, dass meine Daten elektronisch verarbeitet werden', 'translation_domain' => 'form'])
            ->add('newsletter', CheckboxType::class, ['required' => false, 'label' => 'Zum Newsletter anmelden', 'translation_domain' => 'form'])
            // ->add('captcha', RecaptchaType::class, [
            // "groups" option is not mandatory

            //])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'weiter', 'translation_domain' => 'form'])
            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if (count($errors) == 0) {
                $adresse->setFin(false);
                $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('workflow_confirm_Email', array('redirect' => $this->generateUrl('ferien_auswahl', array('slug'=>$stadt->getSlug())), 'uid' => $adresse->getUid(), 'stadt' => $stadt->getId()));
                //$response = $this->redirectToRoute('loerrach_workflow_schulen');
                $response->headers->setCookie($cookie);
                return $response;
            } else {
                // return $this->redirectToRoute('task_success');
            }

        }

        return $this->render('ferien/adresse.html.twig', array('stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/{slug}/ferien/auswahl", name="ferien_auswahl", methods={"GET"})
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

        return $this->render('ferien/ferien.html.twig', array('org' => $org, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $kinder));

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
                if (count($errors) === 0) {

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generateUrl('ferien_kind_programm', array('slug' => $stadt->getSlug(),'kind_id' => $kind->getId()))));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('ferien/kindForm.html.twig', array('stadt' => $stadt, 'form' => $form->createView()));
    }


    /**
     * @Route("/{slug}/ferien/kind/programm",name="ferien_kind_programm",methods={"GET","POST"})
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


        $dates = array();
        $dates = $this->getDoctrine()->getRepository(Ferienblock::class)->findFerienblocksFromToday($stadt);
        $today = new \DateTime('today');


        return $this->render('ferien/blocks.html.twig', array('kind' => $kind, 'dates' => $dates, 'stadt' => $stadt, 'today'=> $today));
    }


    /**
     * @Route("/loerrach/kinder/block/toggle",name="ferien_kinder_block_toggle",methods={"PATCH"})
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

                $cookie_seccode = explode('.', $request->cookies->get('SecID'));


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
