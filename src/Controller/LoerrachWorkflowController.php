<?php

namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachKind;
use App\Form\Type\StadtType;
use App\Service\IcsService;
use App\Service\MailerService;
use App\Service\PrintService;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Cookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;


class LoerrachWorkflowController extends AbstractController
{
    private $einkommensgruppen;
    private  $translator;
    private $beruflicheSituation;

    public function __construct(TranslatorInterface $translator)
    {
        $this->einkommensgruppen = array(
            '0 - 1.000 Euro' => 0,
            '1.001 - 2.000 Euro' => 1,
            '2.001 . 3.000 Euro' => 2,
            '3.001 . 5.000 Euro' => 3,
            'mehr als 5.001 Euro' => 4,
            );
        $this->beruflicheSituation = array(
            $translator->trans('Berufstätig')=>1,
            $translator->trans('Arbeitssuchend')=>2,
            $translator->trans('Keine Angabe')=>0
            );
        $this->translator = $translator;
    }

    /**
     * @Route("/loerrach/adresse",name="loerrach_workflow_adresse",methods={"GET","POST"})
     */
    public function adresseAction(Request $request, ValidatorInterface $validator)
    {


        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'Loerrach'));
        $schuljahr = $this->getSchuljahr($stadt);
        if ($schuljahr == null){
            return $this->redirectToRoute('workflow_closed', array('slug'=>$stadt->getSlug()));
        }

        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }


        //Add SecCode into if to create a SecCode the first time to be not "null"
        if($adresse->getUid() == null){
            $adresse->setUid(md5(uniqid()))
                ->setAngemeldet(false);
            $adresse->setCreatedAt(new \DateTime());
        }

        $form = $this->createFormBuilder($adresse)
            ->add('email',EmailType::class, ['required'=>true,'label' => 'Email', 'translation_domain' => 'form'])
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'translation_domain' => 'form','help'=>'Das ist eine Hilfe für diese Frage im Form'])
            ->add('name', TextType::class, ['label' => 'Nachname', 'translation_domain' => 'form'])
            ->add('strasse', TextType::class, ['label' => 'Straße', 'translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class, ['required'=>false,'label' => 'Adresszusatz', 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'translation_domain' => 'form'])
            ->add('stadt', TextType::class, ['label' => 'Stadt', 'translation_domain' => 'form','help'=>'Das ist eine Hilfe für diese Frage im Form'])
            ->add('einkommen', ChoiceType::class, [
                'choices' => $this->einkommensgruppen, 'label' => 'Brutto Familieneinkommen pro Monat', 'translation_domain' => 'form'])
            ->add('beruflicheSituation', ChoiceType::class, ['choices'=>$this->beruflicheSituation,'required'=>true,'label' => 'Berufliche Situation der Eltern', 'translation_domain' => 'form'])
            ->add('kinderImKiga', CheckboxType::class, ['required'=>false,'label' => 'Weiteres Kind in einer kostenpflichtiger öffentlichen Kindergarteneinrichtung', 'translation_domain' => 'form'])
            ->add('buk', CheckboxType::class, ['required'=>false,'label' => 'Ich bin Bildungs- und Teilhabepaket (BUT) Empfänger', 'translation_domain' => 'form'])
            ->add('alleinerziehend', CheckboxType::class, ['required'=>false,'label' => 'Ich bin Alleinerziehend', 'translation_domain' => 'form'])
            ->add('notfallName', TextType::class, ['required'=>true,'label' => 'Name und Beziehung des Notfallkontakt', 'translation_domain' => 'form'])
            ->add('notfallkontakt', TextType::class, ['required'=>true,'label' => 'Notfalltelefonnummer', 'translation_domain' => 'form'])
            ->add('iban', TextType::class, ['required'=>true,'label' => 'IBAN für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('bic', TextType::class, ['required'=>true,'label' => 'BIC für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('kontoinhaber', TextType::class, ['required'=>true,'label' => 'Kontoinhaber für das Lastschriftmandat', 'translation_domain' => 'form'])
            ->add('abholberechtigter', TextareaType::class,['required'=>false,'label'=>'Abholberechtiger','translation_domain' => 'form','attr'=>['rows'=>6]])
            ->add('sepaInfo', CheckboxType::class, ['required'=>true,'label' => 'SEPA-LAstschrift Mandat wird elektromisch erteilt', 'translation_domain' => 'form'])
            ->add('gdpr', CheckboxType::class, ['required'=>true,'label' => 'Ich nehme zur Kenntniss, dass meine Daten elektronisch verarbeitet werden', 'translation_domain' => 'form'])
            ->add('newsletter', CheckboxType::class, ['required'=>false,'label' => 'Zum Newsletter anmelden', 'translation_domain' => 'form'])
            // ->add('captcha', RecaptchaType::class, [
            // "groups" option is not mandatory

            //])
            ->add('submit', SubmitType::class, ['attr'=> array('class'=> 'btn btn-outline-primary'), 'label' => 'weiter', 'translation_domain' => 'form'])
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
                $response= $this->redirectToRoute('workflow_confirm_Email',array('redirect'=>$this->generateUrl('loerrach_workflow_schulen'),'uid'=>$adresse->getUid(),'stadt'=>$stadt->getId()));
                //$response = $this->redirectToRoute('loerrach_workflow_schulen');
                $response->headers->setCookie($cookie);
                return $response;
            } else {
                // return $this->redirectToRoute('task_success');
            }

        }

        return $this->render('workflow/loerrach/adresse.html.twig', array('stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/loerrach/schulen",name="loerrach_workflow_schulen",methods={"GET"})
     */
    public function schulenAction(Request $request, ValidatorInterface $validator)
    {

        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));

        // Load all schools from the city into the controller as $schulen
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('stadt' => $stadt,'deleted'=>false));

        $schuljahr = $this->getSchuljahr($stadt);
        if ($schuljahr == null){
            return $this->redirectToRoute('workflow_closed', array('slug'=>$stadt->getSlug()));
        }

        // load parent address data into controller as $adresse
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }
        $kinder = array();
        if ($request->cookies->get('KindID')){
            $cookie_kind = explode('.', $request->cookies->get('KindID'));
            $kinder = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('id'=>$cookie_kind[0]));
        }else{
            $kinder = $adresse->getKinds()->toArray();
        }

        $renderKinder = array();
        foreach ($kinder as $data) {
            $renderKinder[$data->getSchule()->getId()][] = $data;
        }
        return $this->render('workflow/loerrach/schulen.html.twig', array('schule' => $schule, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $renderKinder));
    }

    /**
     * @Route("/loerrach/schulen/kind/neu",name="loerrach_workflow_schulen_kind_neu",methods={"GET","POST"})
     */
    public function neukindAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule_id'));

        $kind = new Kind();
        $kind->setEltern($adresse);
        $kind->setSchule($schule);
        $form = $this->createForm(LoerrachKind::class, $kind,array('action'=>$this->generateUrl('loerrach_workflow_schulen_kind_neu',array('schule_id'=>$schule->getId()))));

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
                    return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generateUrl('loerrach_workflow_schulen_kind_zeitblock', array('kind_id' => $kind->getId()))));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('schule' => $schule, 'form' => $form->createView()));
    }

    /**
     * @Route("/loerrach/schulen/kind/edit",name="loerrach_workflow_schulen_kind_edit",methods={"GET","POST"})
     */
    public function editkindAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $form = $this->createForm(LoerrachKind::class, $kind,array(
            'action'=>$this->generateUrl('loerrach_workflow_schulen_kind_edit',array('kind_id'=>$kind->getId()))
        ));
        $form->remove('art');


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
                    $text = $translator->trans('Erfolgreich geändert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/loerrach/schulen/kind/delete",name="loerrach_workflow_kind_delete",methods={"GET"})
     */
    public function deleteAction(Request $request, ValidatorInterface $validator)
    {
        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $em = $this->getDoctrine()->getManager();
        $em->remove($kind);
        $em->flush();
        return $this->redirectToRoute('loerrach_workflow_schulen');
    }

    /**
     * @Route("/loerrach/schulen/kind/zeitblock",name="loerrach_workflow_schulen_kind_zeitblock",methods={"GET"})
     */
    public function kindzeitblockAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {

        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $schule = $kind->getSchule();
        //$stadt = $adresse->getStadt();
        //$schuljahr = $this->getSchuljahr($stadt);






        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));
        $schuljahr = $this->getSchuljahr($stadt);
        $req = array(
            'deleted'=>false,
            'active'=>$schuljahr,
            'schule' => $schule,
            );
        $block = array();
        if ($kind->getArt() == 1) {
            $req['ganztag']= 0;
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von'=>'asc'));
            $req['ganztag'] = $kind->getArt();
            $block = array_merge($block, $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von'=>'asc')));

        } elseif ($kind->getArt() == 2) {
            $req['ganztag']= $kind->getArt();
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von'=>'asc'));

        }

        $renderBlocks = array();
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->render('workflow/loerrach/blockKinder.html.twig', array('kind' => $kind, 'blocks' => $renderBlocks));
    }

    /**
     * @Route("/loerrach/kinder/block/toggle",name="loerrach_workflow_kinder_block_toggle",methods={"GET"})
     */
    public function kinderblocktoggleAction(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $result = array(
            'text' => $translator->trans('Betreuungsblock erfolgreich gespeichert'),
            'error'=>0,
        );
        try {
            //Include Parents in this route
            $adresse = new Stammdaten;
            if ($this->getStammdatenFromCookie($request)) {
                $adresse = $this->getStammdatenFromCookie($request);
            }

            $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kinder_id')));
         $result['preisUrl']= $this->generateUrl('loerrach_workflow_preis_einKind',array('kind_id'=>$kind->getId()));
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block_id'));

            if (in_array($block, $kind->getZeitblocks()->toArray())) {
                $kind->removeZeitblock($block);
            } else {
                $kind->addZeitblock($block);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($kind);
            $em->flush();
            $blocks = $kind->getZeitblocks();
            $blocks2 = array();
            foreach ($blocks as $data){
                if($data->getGanztag() != 0){
                    $blocks2[] = $data;
                }
            }

            if (sizeof($blocks2) < 2){
                $result['text'] = $translator->trans('Bitte weiteren Betreuungsblock auswählen (Mindestens zwei Blöcke müssen ausgewählt werden)');
            $result['error'] = 2;
            }
        } catch (\Exception $e) {
            $result['text'] = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            $result['error'] = 1;
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/loerrach/zusammenfassung",name="loerrach_workflow_zusammenfassung",methods={"GET"})
     */
    public function zusammenfassungAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));

        //Check for Anmeldung open
        $schuljahr = $this->getSchuljahr($stadt);
        if ($schuljahr == null){
            return $this->redirectToRoute('workflow_closed', array('slug'=>$stadt->getSlug()));
        }

        $adresse = new Stammdaten;
        //Include Parents in this route
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }

        $kind = $adresse->getKinds();

        $preis = 0;
        foreach ($kind as $data){
            $preis +=$data->getPreisforBetreuung();
        }

        $error = false;
        foreach ($kind as $data){
            if ($data->getBetreungsblocks() < 2){
                $error= true;
                break;
            }
        }
        return $this->render('workflow/loerrach/zusammenfassung.html.twig', array('einkommen'=>array_flip($this->einkommensgruppen),'kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt, 'preis'=>$preis, 'error'=>$error));
    }

    /**
     * @Route("/loerrach/abschluss",name="loerrach_workflow_abschluss",methods={"GET","POST"})
     */
    public function abschlussAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator,MailerService $mailer, TCPDFController $tcpdf, PrintService $print,IcsService $icsService)
    {
        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));

        //Check for Anmeldung open
        $schuljahr = $this->getSchuljahr($stadt);
        if ($schuljahr == null){
            return $this->redirectToRoute('workflow_closed', array('slug'=>$stadt->getSlug()));
        }

        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }

        $kind = $adresse->getKinds();
        foreach ($kind as $data){
            if ($data->getBetreungsblocks() < 2){
               $this->redirectToRoute('loerrach_workflow_zusammenfassung');
            }
        }
        // Daten speichern und fixieren
        $em = $this->getDoctrine()->getManager();
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
        $adresse->setFin(true);
        foreach ($kind as $data){
            $data->setFin(true);
            $em->persist($data);
        }
      $em->persist($adresse);
        $em->flush();
        $attachment = array();
        $ical = array();

        foreach ($kind as $data) {
            $fileName = $data->getVorname().'_'.$data->getNachname().'_'.$data->getSchule()->getName().'.pdf';

            $pdf = $print->printAnmeldebestätigung(
                $data,
                $adresse,
                $stadt,
                $tcpdf,
                $fileName,
                $this->einkommensgruppen,
                'S'
            );
              $attachment[] = (new \Swift_Attachment())
                ->setFilename($fileName.'.pdf')
                ->setContentType('application/pdf')
                ->setBody($pdf);


                foreach ($data->getZeitblocks() as $data2) {
                  $startDate = $data2->getFirstDate()->format('Ymd');
                    $icsService->add(
                        array(
                            'location' => $data2->getSchule()->getOrganisation()->getName(),
                            'description' => $data2->getGanztag()== 0?$this->translator->trans('Mittagessen'):$this->translator->trans(
                                'Betreuung'),
                            'dtstart' =>$startDate.'T'.$data2->getVon()->format('His') ,
                            'dtend' => $startDate.'T'.$data2->getBis()->format('His'),
                            'summary' =>  $data2->getGanztag()== 0?$this->translator->trans('Mittagessen'):$this->translator->trans(
                                'Betreuung'),
                            'url' => '',
                            'rrule'=>'FREQ=WEEKLY;UNTIL='.$data2->getActive()->getBis()->format('Ymd').'T000000'
                        )
                    );
                }

                $attachment[] = (new \Swift_Attachment())
                    ->setFilename($data->getVorname().' '. $data->getNachname().'.ics')
                    ->setContentType('text/calendar')
                    ->setBody($icsService->to_string());

                $icsService = new IcsService();

        }
        $mailBetreff = $translator->trans('Anmeldebestätigung der Schulkindbetreuung für ').$adresse->getVorname(). ' '. $adresse->getName();
        $mailContent = $this->renderView('email/anmeldebestatigung.html.twig',array('eltern'=>$adresse,'kinder'=>$kind,'stadt'=>$stadt));
        $mailer->sendEmail( 'info@h2-invent.com', $adresse->getEmail(), $mailBetreff, $mailContent,$attachment);


        $response = $this->render('workflow/abschluss.html.twig', array('kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt));
        $response->headers->clearCookie('UserID');
        $response->headers->clearCookie('SecID');
        $response->headers->clearCookie('KindID');
        return $response;

    }
    /**
     * @Route("/loerrach/ics",name="loerrach_workflow_ics",methods={"GET","POST"})
     */
    public function ics(Request $request, ValidatorInterface $validator, TranslatorInterface $translator,MailerService $mailer, TCPDFController $tcpdf, PrintService $print,IcsService $icsService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));

        //Check for Anmeldung open
        $schuljahr = $this->getSchuljahr($stadt);
        if ($schuljahr == null){
            return $this->redirectToRoute('workflow_closed', array('slug'=>$stadt->getSlug()));
        }

        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }

        $kind = $adresse->getKinds();

        foreach ($kind as $data) {

            foreach ($data->getZeitblocks() as $data2) {
                $icsService->add(
                    array(
                        'location' => 'Organisation',
                        'description' => 'Betreuung',
                        'dtstart' =>$data2->getActive()->getVon()->format('Ymd').'T'.$data2->getVon()->format('His') ,
                        'dtend' => $data2->getActive()->getVon()->format('Ymd').'T'.$data2->getBis()->format('His'),
                        'summary' => 'Betreung',
                        'url' => '',
                        'rrule'=>'FREQ=WEEKLY;UNTIL='.$data2->getActive()->getBis()->format('Ymd').'T000000'
                        )
                );
            }

            $ical[] = array('name'=>$data->getVorname().' '. $data->getNachname(),'ics'=>$icsService->to_string());
            $icsService = new IcsService();
        }



    }
    /**
     * @Route("/loerrach/berechnung/einKind",name="loerrach_workflow_preis_einKind",methods={"GET"})
     */
    public function berechnungAction(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => 'loerrach'));
        $result = array(
            'error' => 0,
            'text' => $translator->trans('Preis erfolgreich berechnet.'),

        );

        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($this->getStammdatenFromCookie($request)) {
            $adresse = $this->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(
            array('id' => $request->get('kind_id'), 'eltern' => $adresse)
        );
        $blocks = $kind->getZeitblocks();
        $betreuung = array();
        $mitagessen = array();
        foreach ($blocks as $data) {
            if ($data->getGanztag() == 0) {
                $mitagessen[] = $data;
            } else {
                $betreuung[] = $data;
            }
        }

// Wenn weniger als zwei Blöcke für das Kind ausgewählt sind
        if(sizeof($betreuung)<2){
            $result['error']= 1;
            $result['text']= $translator->trans('Bitte weiteren Betreuungsblock auswählen (Mindestens zwei Blöcke müssen ausgewählt werden)');
            return new JsonResponse($result);
        }
       $result['betrag']= number_format($kind->getPreisforBetreuung(),2,',','.');
        return new JsonResponse($result);

    }

    /**
     * @Route("/loerrach/berechnung/printPdf",name="loerrach_workflow_print_pdf",methods={"GET"})
     */
    public function prinPdf(Request $request, ValidatorInterface $validator,TranslatorInterface $translator, TCPDFController $tcpdf, PrintService $print)
    {
        $elter = $this->getStammdatenFromCookie($request);
        $stadt = $elter->getKinds()[0]->getSchule()->getStadt();
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern'=>$elter,'id'=>$request->get(
            'id')));
        $fileName = $kind->getVorname().'_'.$kind->getNachname().'_'.$kind->getSchule()->getName().'.pdf';

        return  $print ->printAnmeldebestätigung($kind, $elter, $stadt, $tcpdf, $fileName, $this->einkommensgruppen,'D');


    }

// Nach UId und Fin fragen
    private function getStammdatenFromCookie(Request $request)
    {
        if ($request->cookies->get('UserID')) {


            $cookie_ar = explode('.', $request->cookies->get('UserID'));
            $hash = hash("sha256", $cookie_ar[0] . $this->getParameter("secret"));
            $search = array('uid'=>$cookie_ar[0]);
            if ($request->cookies->get('KindID') && $request->cookies->get('SecID')  ) {


                $cookie_kind = explode('.', $request->cookies->get('KindID'));
                $hash_kind = hash("sha256", $cookie_kind[0] . $this->getParameter("secret"));

                $cookie_seccode = explode('.', $request->cookies->get('SecID'));
                $hash_seccode = hash("sha256", $cookie_seccode[0] . $this->getParameter("secret"));

                if (
                    $this->getUser()
                    && $this->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')
                    && $hash_kind == $cookie_kind[1]
                    && $hash_seccode == $cookie_seccode[1]){
                    $search['secCode'] = $cookie_seccode[0];
                }
            }else{
                $search['fin'] = false;
            }

            if ($hash == $cookie_ar[1]) {
                $adresse = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy($search);
                return $adresse;
            }

            return null;
        }
        return null;
    }
    private function getSchuljahr($stadt)
    {
        if ($this->getUser() && $this->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')){
            return $this->getDoctrine()->getRepository(Active::class)->findSchuljahrFromCity($stadt);
        }else{
            return $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        }
    }



}
