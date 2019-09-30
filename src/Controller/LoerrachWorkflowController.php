<?php
namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\StadtType;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoerrachWorkflowController  extends AbstractController
{
    /**
     * @Route("/loerrach/adresse",name="loerrach_workflow_adresse",methods={"GET","POST"})
     */
    public function adresseAction(Request $request,ValidatorInterface $validator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug'=>'Loerrach'));
        $adresse = new Stammdaten;
        if($this->getStammdatenFromCookie($request)){
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $adresse->setUid(md5(uniqid()))
            ->setAngemeldet(false);
        $adresse->setCreatedAt(new \DateTime());
        $form = $this->createFormBuilder($adresse)
            ->add('name', TextType::class,['label'=>'Name','translation_domain' => 'form'])
            ->add('vorname', TextType::class,['label'=>'Vorname','translation_domain' => 'form'])
            ->add('strasse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('einkommen', NumberType::class,['label'=>'Netto Haushaltseinkommen','translation_domain' => 'form'])
            ->add('kinderImKiga', CheckboxType::class,['label'=>'Kind im Kindergarten','translation_domain' => 'form'])
            ->add('buk', CheckboxType::class,['label'=>'BUK Empfänger','translation_domain' => 'form'])
            ->add('beruflicheSituation', TextType::class,['label'=>'Berufliche Situation der Eltern','translation_domain' => 'form'])
            ->add('notfallkontakt', TextType::class,['label'=>'Notfallkontakt','translation_domain' => 'form'])
            ->add('iban', TextType::class,['label'=>'IBAN für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('bic', TextType::class,['label'=>'BIC für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('kontoinhaber', TextType::class,['label'=>'Kontoinhaber für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('sepaInfo', CheckboxType::class,['label'=>'SEPA-LAstschrift Mandat wird elektromisch erteilt','translation_domain' => 'form'])
           // ->add('captcha', RecaptchaType::class, [
                // "groups" option is not mandatory

            //])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])

            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if(count($errors)== 0) {
                $adresse->setFin(false);
                $cookie = new Cookie ('UserID',$adresse->getUid().".".hash("sha256",$adresse->getUid().$this->getParameter("secret")),time()+60*60*24*365);
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('loerrach_workflow_schulen');
                $response->headers->setCookie($cookie);
                return $response;
            }else{
                // return $this->redirectToRoute('task_success');
            }

        }

        return $this->render('workflow/loerrach/adresse.html.twig',array('stadt'=>$stadt,'form' => $form->createView(),'errors'=>$errors));
    }

    /**
     * @Route("/loerrach/schulen",name="loerrach_workflow_schulen",methods={"GET"})
     */
    public function schulenAction(Request $request,ValidatorInterface $validator){

        // Load the data from the city into the controller as $stadt
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug'=>'loerrach'));

        // Load all schools from the city into the controller as $schulen
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('stadt'=>$stadt));

        // load parent address data into controller as $adresse
        $adresse = new Stammdaten;
        if($this->getStammdatenFromCookie($request)){
            $adresse = $this->getStammdatenFromCookie($request);
        }else{
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }

        return $this->render('workflow/loerrach/schulen.html.twig',array('schule'=>$schule, 'stadt'=>$stadt, 'adresse'=>$adresse));
    }

    /**
     * @Route("/loerrach/schulen/kind/neu",name="loerrach_workflow_schulen_kind_neu",methods={"GET", "POST"})
     */
    public function neukindAction(Request $request,ValidatorInterface $validator,TranslatorInterface $translator){
    $adresse = new Stammdaten;

        //Include Parents in this route
        if($this->getStammdatenFromCookie($request)){
            $adresse = $this->getStammdatenFromCookie($request);
        }

        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule_id'));
        $block = $schule->getZeitblocks();

        $kind = new Kind();
        $kind->setEltern($adresse);
        $form = $this->createFormBuilder($kind)
            ->add('vorname', TextType::class,['label'=>'Vorname','translation_domain' => 'form'])
            ->add('nachname', TextType::class,['label'=>'Name','translation_domain' => 'form'])
            ->add('klasse', NumberType::class,['label'=>'Klasse','translation_domain' => 'form'])
            ->add('art', ChoiceType::class, [
                'choices'  => [
                    'Ganztagsbetreuung' => 1,
                    'Halbtagsbetreuung' => 2,
                ],'label'=>'Art der Betreuung','translation_domain' => 'form'])
            ->add('geburtstag', BirthdayType::class,['label'=>'Geburtstag','translation_domain' => 'form'])
            ->add('allergie', TextType::class,['label'=>'Allergien','translation_domain' => 'form'])
            ->add('medikamente', TextType::class,['label'=>'Medikamente','translation_domain' => 'form'])
            ->setAction($this->generateUrl('loerrach_workflow_schulen_kind_neu', array('schule_id'=>$schule->getId())))

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
                $response = $this->redirectToRoute('loerrach_workflow_schulen');
                $response->headers->setCookie($cookie);
                return $response;
            } else {
                // return $this->redirectToRoute('task_success');
            }
        }
        return $this->render('workflow/loerrach/kindForm.html.twig',array('schule'=>$schule, 'form'=>$form->createView()));
    }


    // Include Parental data
    private function getStammdatenFromCookie(Request $request){
        if ($request->cookies->get('UserID')){
            $cookie_ar = explode('.', $request->cookies->get('UserID'));

            $hash = hash("sha256", $cookie_ar[0].$this->getParameter("secret"));
            if ($hash == $cookie_ar[1]){
                $adresse = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid'=>$cookie_ar[0]));
                return $adresse;
            }
            return null;
        }
        return null;
    }
}