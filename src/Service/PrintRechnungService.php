<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Stadt;
use App\Entity\Stammdaten;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PrintRechnungService
{

    private $templating;
    private  $translator;
    protected $parameterBag;
    private $pdf;
    public function __construct(TCPDFController $tcpdf,\Swift_Mailer $mailer, EngineInterface $templating,TranslatorInterface $translator,ParameterBagInterface $parameterBag)
    {

        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->pdf = $tcpdf;
    }

    public function printRechnung(  $fileName, Organisation $organisation, Rechnung $rechnung, $type = 'D' )
    {

        $eltern = $rechnung->getStammdaten();
        $pdf = $this->pdf;
        $pdf = $this->pdf->create();
        $pdf->setOrganisation($organisation);
        $fileName = $organisation->getName() .'_'.$rechnung->getRechnungsnummer();
        //$pdf-> = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //todo hier musss der Test raus
        $pdf->SetAuthor($fileName);
        $pdf->SetTitle($fileName);
        $pdf->SetSubject($fileName);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(20, 15, 20, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(1, 1);

        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage();
        $adressComp = '<p><small>'.$organisation->getName().' | '.$organisation->getAdresse().$organisation->getAdresszusatz(
            ).' | '.$organisation->getPlz().(' ').$organisation->getOrt().'</small><br><br>';

        $adressComp = $adressComp.$eltern->getVorname().' '.$eltern->getName();
        $adressComp .= '<br>'.$eltern->getStrasse();
        $adressComp = $adressComp.($eltern->getAdresszusatz() ? ('<br>'.$eltern->getAdresszusatz()) : '');
        $adressComp .= '<br>'.$eltern->getPlz().' '.$eltern->getStadt();

        $adressComp = $adressComp.'</p>';

        $pdf->writeHTMLCell(
             0,
            0,
            20,
            50,
            $adressComp,
             0,
            1,
            0,
            true,
            '',
             true
        );

        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        $logo = '';
        if ($organisation->getImage()) {
            $logo = $this->templating->render('pdf/img.html.twig', array('stadt' => $organisation));
        }
        $logo = $this->parameterBag->get('kernel.project_dir').'/public'.$logo;
        $im = file_get_contents($logo);
        $imdata = base64_encode($im);
        $imgdata = base64_decode($imdata);

        $pdf->Image('@'.$imgdata,140,20,50);

          $kontaktDaten = '<table cellspacing="3px">'.

            '<tr>'.'<td align="right">'.$this->translator->trans('Rechnungsdatum').': </td><td  align="left" >'.$rechnung->getCreatedAt()->format('d.m.Y').'</td></tr>'.
              '<tr>'.'<td align="right">'.$this->translator->trans('Rechnungsnummer').': </td><td  align="left" >'.$rechnung->getRechnungsnummer().'</td></tr>'.

              '<tr>'.'<td align="right">'.$this->translator->trans('Betreuende Organisation').': </td><td  align="left" >'.$organisation->getName().'</td></tr>'.
            '<tr>'.'<td align="right">'.$this->translator->trans('Ansprechpartner').': </td><td  align="left" >'. $organisation->getAnsprechpartner().'</td></tr>'.
            '<tr>'.'<td align="right">'.$this->translator->trans('Telefonnummer').': </td><td  align="left" >'. $organisation->getTelefon().'</td></tr>';
        '<tr>'.'<td align="right">'.$this->translator->trans('Email').': </td><td  align="left" >'. $organisation->getEmail().'</td></tr>';
        $kontaktDaten .= '</table>';
        $pdf->writeHTMLCell(
           300,
             0,
             10,
             65,
            $kontaktDaten,
            0,
             1,
             0,
            true,
            'L',
            true
        );

        $table = $this->templating->render('rechnung/tabelle.html.twig',array('rechnung'=>$rechnung,'organisation'=>$organisation));
        $pdf->writeHTMLCell(
            0,
            0,
             20,
            100,
            $table,
            0,
            1,
             0,
             true,
             '',
             true
        );

        // hier beginnt die Seite mit den Kindern


  return  $pdf->Output($fileName.".pdf", $type); // This will output the PDF as a Download
    }


}
