<?php

namespace App\Controller;

use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Service\PrintRechnungService;
use App\Service\SepaCreateService;
use App\Service\SepaExcel;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class SepaDetailController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/org_accounting/sepa/detail", name="accounting_sepa_detail")
     */
    public function index(Request $request,SepaCreateService $sepaCreateService)
    {
        set_time_limit(600);
       $sepa = $this->managerRegistry->getRepository(Sepa::class)->find($request->get('id'));
       if($sepa->getOrganisation() != $this->getUser()->getOrganisation()){
           throw new \Exception('Wrong Organisation');
       }
       $simDate = (clone $sepa->getVon())->modify('first day of next month');

       $stammdatenChange = $sepaCreateService->diffToThisMonth($sepa,$simDate);
       return $this->render('sepa_detail/detail.html.twig',array('sepa'=>$sepa,'diffs'=>$stammdatenChange,'simDate'=>$simDate));
    }
    /**
     * @Route("/org_accounting/print/detail", name="accounting_sepa_print")
     */
    public function print(Request $request,PrintRechnungService $printRechnungService)
    {
        $rechnung = $this->managerRegistry->getRepository(Rechnung::class)->find($request->get('id'));

        if($rechnung->getKinder()->toArray()[0]->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }

        return $printRechnungService->printRechnung('Test',$rechnung->getKinder()->toArray()[0]->getSchule()->getOrganisation(),$rechnung,'D');
    }
    /**
     * @Route("/org_accounting/print/sepaXML", name="accounting_sepa_printXML")
     */
    public function printXML(Request $request,PrintRechnungService $printRechnungService)
    {
        $sepa = $this->managerRegistry->getRepository(Sepa::class)->find($request->get('id'));
        if($sepa->getOrganisation() != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        $response = new Response($sepa->getSepaXML());
        $filename= 'SEPA-'.$sepa->getCreatedAt()->format('dmY_H_i_s');
        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename.'.xml'
        );
        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;


    }
    /**
     * @Route("/org_accounting/print/excel", name="accounting_sepa_printExcel")
     */
    public function printExcel(Request $request,PrintRechnungService $printRechnungService,SepaExcel $sepaExcel)
    {
        $sepa = $this->managerRegistry->getRepository(Sepa::class)->find($request->get('sepa_id'));
        if($sepa->getOrganisation() != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        return $this->file($sepaExcel->generateExcel($sepa),'SEPA_ID'.$sepa->getId().'.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
