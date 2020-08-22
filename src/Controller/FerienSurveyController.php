<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienSurveyController extends AbstractController
{

    /**
     * @Route("/org_ferien/edit/question", name="ferien_management_question", methods={"GET"})
     */
    public function ferienblockFragen(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));


        return $this->render('ferien_survey/index.html.twig',array('ferien'=>$ferienblock));
    }
    /**
     * @Route("/org_ferien/edit/question/save", name="ferien_management_question_save", methods={"POST"})
     */
    public function ferienblockFragenSave(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        try {
            $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('id'), 'organisation' => $organisation));
            $ferienblock->setIndividualQuestions($request->get('survey'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($ferienblock);
            $em->flush();
            return new JsonResponse(array('error'=>false,'text'=>$translator->trans('Erfolgreich gespeichert')));
        }catch (\Exception $e){
            return new JsonResponse(array('error'=>true,'text'=>$translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
        }


    }
}
