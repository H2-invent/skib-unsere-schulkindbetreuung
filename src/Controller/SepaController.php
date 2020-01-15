<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\SepaType;
use App\Service\MailerService;
use App\Service\PrintRechnungService;
use App\Service\SepaCreateService;
use App\Service\SEPASimpleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class SepaController extends AbstractController
{
    /**
     * @Route("/org_accounting/overview", name="accounting_overview",methods={"GET","POST"})
     */
    public function index( Request $request,SepaCreateService $sepaCreateService, ValidatorInterface $validator)
    {
        set_time_limit(600);
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $sepa = new Sepa();
        $sepa->setOrganisation($organisation);
        $form = $this->createForm(SepaType::class, $sepa);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($sepa);
            if(count($errors)== 0) {
                $sepa = $form->getData();
                $sepa->setBis((clone $sepa->getVon())->modify('last day of this month'));
                $result = $sepaCreateService->calcSepa($sepa);

                return $this->redirectToRoute('accounting_overview',array('id'=>$organisation->getId(),'snack'=>$result));
            }
        }
        $sepaData = $this->getDoctrine()->getRepository(Sepa::class)->findBy(array('organisation'=>$organisation));
        return $this->render('sepa/show.html.twig',array('form'=>$form->createView(),'sepa'=>$sepaData));
    }

}
