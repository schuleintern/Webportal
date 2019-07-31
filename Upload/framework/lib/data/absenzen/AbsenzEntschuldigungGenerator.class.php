<?php

class AbsenzEntschuldigungGenerator {
    
    /**
     * 
     * @var array
     */
    private $krankmeldungen = [];
    
    /**
     * 
     * @var Absenz[]
     */
    private $absenzen = [];
    
    private $pdf = null;
    
    private $needAttest = false;
    
    public function __construct() {
        $this->pdf = new PrintNormalPageA4WithHeader("Entschuldigung");
        $this->pdf->setPrintedDateInFooter();
        
        
    }
    
    /**
     * 
     * @param String[][] $krankmeldung
     */
    public function addKrankmeldung($krankmeldung) {
        $this->krankmeldungen[] = $krankmeldung;
    }
    
    /**
     * 
     * @param Absenz $absenz
     */
    public function addAbsenz($absenz) {
        $this->absenzen[] = $absenz;
    }
    
    private function getBarcodeParams($text) {
        $params = $this->pdf->serializeTCPDFtagParameters(array($text, 'C128', '', '', 30, 12, 0.4, array('position'=>'S', 'border'=>true, 'padding'=>1, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>1), 'N'));
        return $params;
    }
    
    private function getAbsenzHTML($schueler, $startDate, $endDate, $barcodeText) {
        
        $entschuldigungHTML = "";
        
        // Barcode HTML: <tcpdf method=\"write1DBarcode\" params=\"" . $this->getBarcodeParams($barcodeText) . "\" />
        
        $hasLNW = false;
        
        if(DB::getSettings()->getBoolean('krankmeldung-hinweis-lnw')) {
            $lnws = Leistungsnachweis::getByClass([$schueler->getKlasse()], $startDate, $endDate);
        
            for($l = 0; $l < sizeof($lnws); $l++) {
                if($lnws[$l]->showForNotTeacher()) {
                    $hasLNW = true;
                    break;
                }
            }
        }
        
        
        
        $entschuldigungHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($startDate) . "<br /><br />
                                
                </td><td>" . DateFunctions::getNaturalDateFromMySQLDate($endDate) . "</td><td>[  ] Krankheit <i>oder</i><br><br><br />[  ] _________________________________<br>";
        
        $entschuldigungHTML .= "[";
        
        if(AbsenzSchuelerInfo::hasAttestpflicht($schueler, $startDate)) {
            $this->needAttest = true;
            $entschuldigungHTML .= " X ";
        }
        else if($hasLNW) {
            $this->needAttest = true;
            $entschuldigungHTML .= " X ";
        }
        else {
            $entschuldigungHTML .= "  ";
        }
        
        $entschuldigungHTML .= "] Ärztliches Attest beiliegend.";
        
        if($hasLNW) $entschuldigungHTML .= " (Angekündigter Leistungsnachweis)";
        
        $entschuldigungHTML .= "</td></tr>";
        
        return $entschuldigungHTML;
    }
    
    public function send() {
        
        
        $entschuldigungHTML = "";
        
        $schueler = null;
        
        $needAttest = false;
        
        
        
        for($i = 0; $i < sizeof($this->krankmeldungen); $i++) {
            $schueler = schueler::getByAsvID($this->krankmeldungen[$i]['krankmeldungSchuelerASVID']);
            
            
            $entschuldigungHTML .= $this->getAbsenzHTML($schueler, $this->krankmeldungen[$i]['krankmeldungDate'], $this->krankmeldungen[$i]['krankmeldungUntilDate'], 'K' . $this->krankmeldungen[$i]['krankmeldungID']);
                       
            
        }
        
        for($i = 0; $i < sizeof($this->absenzen); $i++) {
            $schueler = $this->absenzen[$i]->getSchueler();
            
            $entschuldigungHTML .= $this->getAbsenzHTML($schueler, $this->absenzen[$i]->getDateAsSQLDate(), $this->absenzen[$i]->getEnddatumAsSQLDate(), "A" . $this->absenzen[$i]->getID());
            
        }
        

        if($schueler == null) {
            $html = "Interner Fehler aufgetreten. (Schüler nicht verfügbar.)";
        }
        else {
    
            $name = $schueler->getCompleteSchuelerName();
            $klasse = $schueler->getKlasse();
            
            $klassenleitungsObjekte = $schueler->getKlassenObjekt()->getKlassenLeitung();
            $klassenleitung = [];
            for($k = 0; $k < sizeof($klassenleitungsObjekte); $k++) {
                $klassenleitung[] = $klassenleitungsObjekte[$k]->getDisplayNameMitAmtsbezeichnung();
            }
            $klassenleitung = implode("<br />", $klassenleitung);
            
            if($schueler->getGeschlecht() == 'm') {
                $file = "entschuldigung_m";
            } else {
                $file = "entschuldigung_f";
            }
            
            eval("\$html = \"". DB::getTPL()->get("krankmeldung/pdf/$file") . "\";");
            
            
        }
        
        

        $this->pdf->setHTMLContent($html);
        $this->pdf->send();
        exit(0);
        
        
    }
    
    
    
}