<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\Templating\EngineInterface;


class SEPAService
{
    public $FCtgyPurp, $FDatum, $FSeqTp;
    private $FBuchungen, $FSumme;
    public function __construct($aCtgyPurp = '', $aDatum ='', $aSeqTp='') {
        $this->FCtgyPurp=$aCtgyPurp;
        $this->FDatum=$aDatum;
        $this->FSeqTp=$aSeqTp;
        $this->FBuchungen=array();
        $this->FSumme=0.00;
    }
    public function Add($aBetrag, $aName, $aIban, $aBic=NULL, $aPurp=NULL, $aRef=NULL, $aVerwend=NULL,
                        $aMandatRef=NULL, $aMandatDate=NULL,
                        $aOldMandatRef=NULL, $aOldName=NULL, $aOldCreditorId=NULL, $aOldIban=NULL, $aOldBic=NULL) {
        $myBuchung=array();
        $myBuchung['BETRAG']=$aBetrag;
        $myBuchung['NAME']=$aName;
        $myBuchung['IBAN']=$aIban;
        $myBuchung['BIC']=$aBic;
        $myBuchung['PURP']=$aPurp;
        $myBuchung['REF']=$aRef;
        $myBuchung['VERWEND']=$aVerwend;
        $myBuchung['MANDATREF']=$aMandatRef;
        $myBuchung['MANDATDATE']=$aMandatDate;
        $myBuchung['OLDMANDATREF']=$aOldMandatRef;
        $myBuchung['OLDNAME']=$aOldName;
        $myBuchung['OLDCREDITORID']=$aOldCreditorId;
        $myBuchung['OLDIBAN']=$aOldIban;
        $myBuchung['OLDBIC']=$aOldBic;
        $this->FBuchungen[]=$myBuchung;
        $this->FSumme+=$aBetrag;
    }
    public function Get($aPmtInfId, $aType, $aAuftraggeber, $aIban, $aBic, $aCreditorId) {
        $myLast=$aType!='TRF';
        $result="    <PmtInf>\n";
        $myPmtInfId=$aPmtInfId;
        if (!empty($this->FCtgyPurp))
            $myPmtInfId.='-'.$this->FCtgyPurp;
        if (!empty($this->FSeqTp))
            $myPmtInfId.='-'.$this->FSeqTp;
        $result.='      <PmtInfId>'.$myPmtInfId."</PmtInfId>\n";
        $result.='      <PmtMtd>'.($myLast?'DD':'TRF')."</PmtMtd>\n";
        $result.='      <NbOfTxs>'.count($this->FBuchungen)."</NbOfTxs>\n";
        $result.='      <CtrlSum>'.sprintf('%.2F', $this->FSumme)."</CtrlSum>\n";
        $result.="      <PmtTpInf>\n";
        $result.="        <SvcLvl>\n";
        $result.="          <Cd>SEPA</Cd>\n";
        $result.="        </SvcLvl>\n";
        if ($myLast) {
            $result.="        <LclInstrm>\n";
            $result.='          <Cd>'.$aType."</Cd>\n";
            $result.="        </LclInstrm>\n";
            $result.='        <SeqTp>'.$this->FSeqTp."</SeqTp>\n";
        }
        if (!empty($this->FCtgyPurp)) {
            $result.="        <CtgyPurp>\n";
            $result.='          <Cd>'.$this->FCtgyPurp."</Cd>\n";
            $result.="        </CtgyPurp>\n";
        }
        $result.="      </PmtTpInf>\n";
        // Ausfuehrungsdatum
        $tag=$myLast?'ReqdColltnDt':'ReqdExctnDt';
        $result.='      <'.$tag.'>'.$this->FDatum.'</'.$tag.">\n";
        // Eigene Daten
        $tag=$myLast?'Cdtr':'Dbtr';
        $result.='      <'.$tag.">\n";
        $result.='        <Nm>'.$aAuftraggeber."</Nm>\n";
        $result.='      </'.$tag.">\n";
        $tag2=$tag.'Acct';
        $result.='      <'.$tag2.">\n";
        $result.="        <Id>\n";
        $result.='          <IBAN>'.$aIban."</IBAN>\n";
        $result.="        </Id>\n";
        $result.='      </'.$tag2.">\n";
        $tag2=$tag.'Agt';
        $result.='      <'.$tag2.">\n";
        $result.="        <FinInstnId>\n";
        if (!empty($aBic))
            $result.='          <BIC>'.$aBic."</BIC>\n";
        else {
            $result.="          <Othr>\n";
            $result.="            <Id>NOTPROVIDED</Id>\n";
            $result.="          </Othr>\n";
        }
        $result.="        </FinInstnId>\n";
        $result.='      </'.$tag2.">\n";
        $result.="      <ChrgBr>SLEV</ChrgBr>\n";
        if ($myLast) {
            $result.="      <CdtrSchmeId>\n";
            $result.="        <Id>\n";
            $result.="          <PrvtId>\n";
            $result.="            <Othr>\n";
            $result.='              <Id>'.$aCreditorId."</Id>\n";
            $result.="              <SchmeNm>\n";
            $result.="                <Prtry>SEPA</Prtry>\n";
            $result.="              </SchmeNm>\n";
            $result.="            </Othr>\n";
            $result.="          </PrvtId>\n";
            $result.="        </Id>\n";
            $result.="      </CdtrSchmeId>\n";
        }
        // Schleife ueber alle Buchungen
        foreach ($this->FBuchungen as $myBuchung) {
            $result.=$myLast?"      <DrctDbtTxInf>\n":"        <CdtTrfTxInf>\n";
            $result.="        <PmtId>\n";
            $result.='          <EndToEndId>'.(empty($myBuchung['REF'])?'NOTPROVIDED':$myBuchung['REF'])."</EndToEndId>\n";
            $result.="        </PmtId>\n";
            if ($myLast) {
                $result.='        <InstdAmt Ccy="EUR">'.sprintf('%.2F', $myBuchung['BETRAG'])."</InstdAmt>\n";
                $result.="        <DrctDbtTx>\n";
                $result.="          <MndtRltdInf>\n";
                $result.='            <MndtId>'.$myBuchung['MANDATREF']."</MndtId>\n";
                $result.='            <DtOfSgntr>'.$myBuchung['MANDATDATE']."</DtOfSgntr>\n";
                $amendmentinfo=!empty($myBuchung['OLDMANDATREF']) || !empty($myBuchung['OLDNAME']) ||
                    !empty($myBuchung['OLDCREDITORID']) || !empty($myBuchung['OLDIBAN']) ||
                    !empty($myBuchung['OLDBIC']);
                $result.='            <AmdmntInd>'.($amendmentinfo?'true':'false')."</AmdmntInd>\n";
                if ($amendmentinfo) {
                    $result.="            <AmdmntInfDtls>\n";
                    if (!empty($myBuchung['OLDMANDATREF']))
                        $result.='              <OrgnlMndtId>'.$myBuchung['OLDMANDATREF']."</OrgnlMndtId>\n";
                    if (!empty($myBuchung['OLDNAME']) or !empty($myBuchung['OLDCREDITORID'])) {
                        $result.="              <OrgnlCdtrSchmeId>\n";
                        if (!empty($myBuchung['OLDNAME']))
                            $result.='                <Nm>'.$myBuchung['OLDNAME']."</Nm>\n";
                        if (!empty($myBuchung['OLDCREDITORID'])) {
                            $result.="                <Id>\n";
                            $result.="                  <PrvtId>\n";
                            $result.="                    <Othr>\n";
                            $result.='                      <Id>'.$myBuchung['OLDCREDITORID']."</Id>\n";
                            $result.="                      <SchmeNm>\n";
                            $result.="                        <Prtry>SEPA</Prtry>\n";
                            $result.="                      </SchmeNm>\n";
                            $result.="                    </Othr>\n";
                            $result.="                  </PrvtId>\n";
                            $result.="                </Id>\n";
                        }
                        $result.="              </OrgnlCdtrSchmeId>\n";
                    }
                    if (!empty($myBuchung['OLDIBAN'])) {
                        $result.="              <OrgnlDbtrAcct>\n";
                        $result.="                <Id>\n";
                        $result.='                  <IBAN>'.$myBuchung['OLDIBAN']."</IBAN>\n";
                        $result.="                </Id>\n";
                        $result.="              </OrgnlDbtrAcct>\n";
                    }
                    if (!empty($myBuchung['OLDBIC'])) {
                        $result.="              <OrgnlDbtrAgt>\n";
                        $result.="                <FinInstnId>\n";
                        $result.="                  <Othr>\n";
                        $result.='                    <Id>'.$myBuchung['OLDBIC']."</Id>\n";
                        $result.="                  </Othr>\n";
                        $result.="                </FinInstnId>\n";
                        $result.="              </OrgnlDbtrAgt>\n";
                    }
                    $result.="            </AmdmntInfDtls>\n";
                }
                $result.="          </MndtRltdInf>\n";
                $result.="        </DrctDbtTx>\n";
            } else {
                $result.="        <Amt>\n";
                $result.='          <InstdAmt Ccy="EUR">'.sprintf('%.2F', $myBuchung['BETRAG'])."</InstdAmt>\n";
                $result.="        </Amt>\n";
            }
            $tag=$myLast?'Dbtr':'Cdtr';
            $tag2=$tag.'Agt';
            if (!empty($myBuchung['BIC'])) {
                $result.='        <'.$tag2.">\n";
                $result.="          <FinInstnId>\n";
                $result.='            <BIC>'.$myBuchung['BIC']."</BIC>\n";
                $result.="          </FinInstnId>\n";
                $result.='        </'.$tag2.">\n";
            } else {
                if ($myLast) {
                    $result.='        <'.$tag2.">\n";
                    $result.="          <FinInstnId>\n";
                    $result.="            <Othr>\n";
                    $result.="              <Id>NOTPROVIDED</Id>\n";
                    $result.="            </Othr>\n";
                    $result.="          </FinInstnId>\n";
                    $result.='        </'.$tag2.">\n";
                }
            }
            $result.='        <'.$tag.">\n";
            $result.='          <Nm>'.$myBuchung['NAME']."</Nm>\n";
            $result.='        </'.$tag.">\n";
            $tag2=$tag.'Acct';
            $result.='        <'.$tag2.">\n";
            $result.="          <Id>\n";
            $result.='            <IBAN>'.$myBuchung['IBAN']."</IBAN>\n";
            $result.="          </Id>\n";
            $result.='        </'.$tag2.">\n";
            if (!empty($myBuchung['PURP'])) {
                $result.="        <Purp>\n";
                $result.='          <Cd>'.$myBuchung['PURP']."</Cd>\n";
                $result.="        </Purp>\n";
            }
            if (!empty($myBuchung['VERWEND'])) {
                $result.="        <RmtInf>\n";
                $result.='          <Ustrd>'.$myBuchung['VERWEND']."</Ustrd>\n";
                $result.="        </RmtInf>\n";
            }
            $result.=$myLast?"      </DrctDbtTxInf>\n":"        </CdtTrfTxInf>\n";
        }
        // Ende der Schleife, Schlussausgaben
        $result.="    </PmtInf>\n";
        return $result;
    }
}


