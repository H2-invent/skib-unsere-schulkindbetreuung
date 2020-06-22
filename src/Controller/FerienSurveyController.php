<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienSurveyController extends AbstractController
{

    /**
     * @Route("/org_ferien/edit/question", name="ferien_management_question", methods={"GET","POST"})
     */
    public function ferienblockFragen(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        if (sizeof($ferienblock->getCustomQuestion()) != 5 || $ferienblock->getAmountCustomQuestion() === null) {
            $ferienblock->setCustomQuestion(array_fill(0, 5, ''));
        }


        return $this->render('ferien_survey/index.html.twig');
    }
}
