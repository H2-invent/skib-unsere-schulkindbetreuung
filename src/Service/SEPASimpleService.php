<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


class SEPASimpleService
{
    private $FVersion, $FPmtInf, $FAnzahl, $FSumme;

    public function __construct()
    {
        $this->FVersion = '1';
        $this->FPmtInf = array();
        $this->FAnzahl = 0;
        $this->FSumme = 0.00;

    }

    private function GetPmtInf($aDatum, $aCtgyPurp, $aSeqTp)
    {
        foreach ($this->FPmtInf as $myPmtInf) {
            if ($myPmtInf->FDatum == $aDatum && $myPmtInf->FCtgyPurp == $aCtgyPurp && $myPmtInf->FSeqTp == $aSeqTp)
                return $myPmtInf;
        }
        $myPmtInf = new \App\Service\SEPAService($aCtgyPurp, $aDatum, $aSeqTp);
        $this->FPmtInf[] = $myPmtInf;
        return $myPmtInf;
    }

    public function Add($aDatum, $aBetrag, $aName, $aIban, $aBic = NULL, $aCtgyPurp = NULL, $aPurp = NULL, $aRef = NULL, $aVerwend = NULL,
                        $aSeqTp = NULL, $aMandatRef = NULL, $aMandatDate = NULL,
                        $aOldMandatRef = NULL, $aOldName = NULL, $aOldCreditorId = NULL, $aOldIban = NULL, $aOldBic = NULL)
    {
        $myPmtInf = $this->GetPmtInf($aDatum, $aCtgyPurp, $aSeqTp);
        $myPmtInf->Add($aBetrag, $aName, $aIban, $aBic, $aPurp, $aRef, $aVerwend,
            $aMandatRef, $aMandatDate,
            $aOldMandatRef, $aOldName, $aOldCreditorId, $aOldIban, $aOldBic);
        $this->FAnzahl++;
        $this->FSumme += $aBetrag;
        if ($aDatum < '2013-11-01')
            $this->FVersion = '2';
    }

    public function GetXML($aType, $aMsgId, $aPmtInfId, $aInitgPty, $aAuftraggeber, $aIban, $aBic, $aCreditorId = NULL)
    {
        // Diverse Vorbelegungen
        $myLast = $aType != 'TRF';
        $pain = $myLast ? 'pain.008.00' . $this->FVersion . '.02' : 'pain.001.00' . $this->FVersion . '.03';
        $urn = 'urn:iso:std:iso:20022:tech:xsd:' . $pain;
        // Header schreiben
        $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $result .= '<Document xmlns="' . $urn . "\"\n";
        $result .= "  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
        $result .= '  xsi:schemaLocation="' . $urn . ' ' . $pain . ".xsd\">\n";
        $result .= $myLast ? "  <CstmrDrctDbtInitn>\n" : "  <CstmrCdtTrfInitn>\n";
        // Group Header
        $result .= "    <GrpHdr>\n";
        $result .= '      <MsgId>' . $aMsgId . "</MsgId>\n";
        $result .= '      <CreDtTm>' . date('Y-m-d\TH:i:s') . "</CreDtTm>\n";
        $result .= '      <NbOfTxs>' . $this->FAnzahl . "</NbOfTxs>\n";
        $result .= '      <CtrlSum>' . sprintf('%.2F', $this->FSumme) . "</CtrlSum>\n";
        $result .= "      <InitgPty>\n";
        $result .= '        <Nm>' . $aInitgPty . "</Nm>\n";
        $result .= "      </InitgPty>\n";
        $result .= "    </GrpHdr>\n";
        // Payment Information(s)
        foreach ($this->FPmtInf as $myPmtInf) {
            $result .= $myPmtInf->Get($aPmtInfId, $aType, $aAuftraggeber, $aIban, $aBic, $aCreditorId);
        }
        // Ende
        $result .= $myLast ? "  </CstmrDrctDbtInitn>\n" : "  </CstmrCdtTrfInitn>\n";
        $result .= "</Document>\n";
        return $result;
    }
}

