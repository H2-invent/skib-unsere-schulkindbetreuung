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
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class workflowController  extends AbstractController
{
    /**
    * @Route("/{slug}/start",name="workflow_start",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
    */
    public function welcomeAction(Request $request, Stadt $stadt)
    {
        return $this->render('workflow/start.html.twig',array('stadt'=>$stadt));
    }
    /**
     * @Route("/{stadt}/adresse",name="workflow_adresse",methods={"GET","POST"})
     */
    public function adresseAction(Request $request, $stadt,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug'=>$stadt));
        $adresse = new Stammdaten;
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
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();

            }else{
                // return $this->redirectToRoute('task_success');
            }

        }

        return $this->render('workflow/adresse.html.twig',array('stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));
    }

}