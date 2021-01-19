<?php 

class TagebuchPDFCreator {
    
    private $startDate = null;
    private $endDate = null;
    private $klasse = null;
    
    private $stundenplaene = [];
    
    
    /**
     *
     * @var TagebuchKlasseEntry[]
     */
    private $allEntries = [];
	
    /**
     * 
     * @param String $start
     * @param String $end
     * @param String $klasse
     */
	public function __construct($start, $end, $klasse) {

	    $this->startDate = $start;
	    $this->endDate = $end;
	    $this->klasse = $klasse;
	    
	}
	
	
	/**
	 * 
	 * @return FileUpload
	 */
	public function createPDF() {
	    
	    $klassen = [$this->klasse];
	    
	    
	    $startDate = $this->startDate;
	    $endDate = $this->endDate;
	    
	    
	    for($i = 0; $i < sizeof($klassen); $i++) {
	        $this->allEntries[$klassen[$i]] = TagebuchKlasseEntry::getAllForGrade($klassen[$i]);
	    }
	    
	    
	    $plaene = [];
	    
	    $plaeneSQL = DB::getDB()->query("SELECT * FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND stundenplanAB >= '" . $startDate . "'");
	    
	    while($planItem = DB::getDB()->fetch_array($plaeneSQL)) {
	        $plan = [
	            'start' => $planItem['stundenplanAb'],
	            'ende' => $planItem['stundenplanBis'],
	            'id' => $planItem['stundenplanID']
	        ];
	        
	        
	        
	        
	        for($k = 0; $k < sizeof($klassen); $k++) {
	            $stunden = [];
	            
	            for($tag = 1; $tag <= 5; $tag++) {
	                for($stunde = 1; $stunde <= stundenplandata::getMaxStunden(); $stunde++) {
	                    $stunden[$tag][$stunde] = [];
	                }
	            }
	            
	            $plan[$klassen[$k]] = $stunden;
	        }
	        
	        $plaene[$planItem['stundenplanID']] = $plan;
	    }
	    
	    
	    
	    foreach($plaene as $id => $plan) {
	        $stundenSQL = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $id . "'");
	        
	        while($stunde = DB::getDB()->fetch_array($stundenSQL)) {
	            $plaene[$stunde['stundenplanID']][$stunde['stundeKlasse']][$stunde['stundeTag']][$stunde['stundeStunde']][] =  [
	                'fach' => $stunde['stundeFach'],
	                'lehrer' => $stunde['stundeLehrer'],
	                'raum' => $stunde['stundeRaum'],
	                'klasse' => $stunde['stundeKlasse']
	            ];
	        }
	        
	        
	    }
	    
	    $this->stundenplaene = $plaene;
	    
	    $gesamtHTML = "";
	    
	    
	    for($i = 0; $i < sizeof($klassen); $i++) {
	        $currentDate = $startDate;
	        
	        
	        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDate, $endDate)) {
	            
	            if(!Ferien::isFerien($currentDate) && !DateFunctions::isSQLDateWeekEnd($currentDate)) {
	                
	                $planAmTag = $this->getStundenplanAtDate($klassen[$i], $currentDate);
	                $entries = $this->getEntriesOfDayAndGrade($currentDate,$klassen[$i]);
	                
	                $klasseHTML = "";
	                
	                
	                $klasseHTML .= "<div style=\"page-break-after: always;\"><h3>Klassentagebuch der Klasse " . $klassen[$i] . " vom " . DateFunctions::getWeekDayNameFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($currentDate)) . ", " . DateFunctions::getNaturalDateFromMySQLDate($currentDate) . "</h3>";
	                
	                $klasseHTML .= "<table border=\"1\" width=\"100%\" cellpadding=\"2\" cellspacing=\"0\"><tr><th width=\"10%\">Stunde</th><th width=\"30%\">Stundenplan</th><th width=\"60%\">Einträge</th></tr>";
	                
	                for($stunde = 1; $stunde <= stundenplandata::getMaxStunden(); $stunde++) {
	                    $klasseHTML .= "<tr><td>" . $stunde . ".</td><td>";
	                    
	                    for($u = 0; $u < sizeof($planAmTag[$stunde]); $u++) {
	                        $klasseHTML .= $planAmTag[$stunde][$u]['fach'] . " bei " . $planAmTag[$stunde][$u]['lehrer'] . " in " . $planAmTag[$stunde][$u]['raum'] . "<br />";
	                    }
	                    
	                    $klasseHTML .= "</td><td>";
	                    
	                    for($e = 0; $e < sizeof($entries); $e++) {
	                        if($entries[$e]->getStunde() == $stunde) {
	                            if($entries[$e]->isAusfall()) {
	                                $klasseHTML .= "<i>Entfall</i> (Lehrkraft: " . $entries[$e]->getTeacher() . ")<br />";
	                            }
	                            else {
	                                $klasseHTML .= "<b>" . $entries[$e]->getFach() . " bei " . $entries[$e]->getTeacher();
	                                if($entries[$e]->isVertretung()) $klasseHTML .= " <i>Vertretung</i></b><br />";
	                                else $klasseHTML .= "</b><br />";
	                                $klasseHTML .= "S: " . $entries[$e]->getStoff() . "<br />";
	                                $klasseHTML .= "HA: " . $entries[$e]->getHausaufgabe() . "<br />";
	                                
	                            }
	                        }
	                    }
	                    
	                    $klasseHTML .= "</td></tr>";
	                    
	                }
	                
	                $klasseHTML .= "</table></div>";
	                
	                $gesamtHTML .= $klasseHTML;
	                
	            }
	            
	            $currentDate = DateFunctions::addOneDayToMySqlDate($currentDate);
	        }
	        
	        
	    }
	    
	    $print = new PrintNormalPageA4WithHeader("Klassentagebuch");
	    $print->setPrintedDateInFooter();
	    $print->showHeaderOnEachPage();
	    $print->setHTMLContent($gesamtHTML);
	   
	    $upload = FileUpload::uploadFromTCPdf('Klassentagebuch - Klasse - ' . $this->klasse . " - von " . $this->startDate . " bis " . $this->endDate. '.pdf', $print);
	    
	    return $upload['uploadobject'];
	    
	    
	}
	
	private function getStundenplanAtDate($klasse, $date) {
	    
	    $currentPlan = [];
	    
	    foreach ($this->stundenplaene as $id => $plan) {
	        if(DateFunctions::isSQLDateAtOrAfterAnother($date, $plan['start'])) {
	            if($plan['ende'] == "" || DateFunctions::isSQLDateAtOrBeforeAnother($date, $plan['ende'])) {
	                $currentPlan = $plan[$klasse];
	            }
	        }
	    }
	    
	    $weekDay = DateFunctions::getWeekDayFromSQLDateISO($date);
	    
	    
	    return $currentPlan[$weekDay];
	    
	}
	
	
	/**
	 *
	 * @param unknown $day
	 * @param unknown $grade
	 * @return TagebuchKlasseEntry[]
	 */
	private function getEntriesOfDayAndGrade($day, $grade) {
	    $entries = [];
	    
	    for($i = 0; $i < sizeof($this->allEntries[$grade]); $i++) {
	        if($this->allEntries[$grade][$i]->getDate() == $day) {
	            $entries[] = $this->allEntries[$grade][$i];
	        }
	    }
	    
	    return $entries;
	}
	
	/**
	 * 
	 * @return FileUpload createdPDF
	 */
	public function createPDF_old() {
	    
	    $currentDay = $this->startDate;
	    
	    $pdf = new PrintNormalPageA4WithHeader('Klassentagebuch - Klasse - ' . $this->startDate . " bis " . $this->endDate);
	    
	    $today = DateFunctions::getTodayAsNaturalDate();
	    
	    $deckblatt = "";
	    
	    
	    
	    $beginNatural = DateFunctions::getNaturalDateFromMySQLDate($this->startDate);
	    $endNatural = DateFunctions::getNaturalDateFromMySQLDate($this->endDate);
	    
	    eval("\$deckblatt = \"" . DB::getTPL()->get("klassentagebuch/auswertung/pdf/pdf/deckblatt") . "\";");
	   
	    
	    
	    
	    $pdf->setHTMLContent($deckblatt);
	    	    
	    
	    while(DateFunctions::isSQLDateAtOrAfterAnother($this->endDate, $currentDay)) {
	        
	        
	        $ferien = Ferien::isFerien($currentDay);
	        
	        $datum = DateFunctions::getNaturalDateFromMySQLDate($currentDay);
	        
	        
	        $dayHTML = "";
	        	        
	        if($ferien != null) {
	            
	            eval("\$dayHTML = \"" . DB::getTPL()->get("klassentagebuch/auswertung/pdf/pdf/ferien") . "\";");
	            
	        }
	        else if(DateFunctions::isSQLDateWeekEnd($currentDay)) {
	            // Nix
	        }
	        else {
	            
	        
	        
	        $stundenplan = stundenplandata::getStundenplanAtDateCached($currentDay);
	        
	        Debugger::debugObject($stundenplan,1);
	        
	        if($stundenplan == null) {
	            eval("\$dayHTML = \"" . DB::getTPL()->get("klassentagebuch/auswertung/pdf/pdf/nostundenplan") . "\";");
	        }
	        else {
	            
    	            $eintraege = TagebuchKlasseEntry::getAllForDateAndGrade($currentDay, $grade);
    	            $tagDerWoche = DateFunctions::getWeekDayFromSQLDateISO($currentDay)-1;
    	            
    	            $unterricht = $stundenplan->getPlan(['grade',$this->klasse]);
    	            $unterricht = $unterricht[$tagDerWoche];
    	            
    	            
    	            
    	            $html = "";
    	            
    	            for($s = 1; $s <= stundenplandata::getMaxStunden(); $s++) {
    	                $html .= "<tr><td>" . $s . "</td><td>";
    	                
    	                for($u = 0; $u < sizeof($unterricht[$s-1]); $u++) {
    	                    $html .= $unterricht[$s-1][$u]['subject'] . " bei " . $unterricht[$s-1][$u]['teacher'] . " in " . $unterricht[$s-1][$u]['room'] . "<br />";
    	                }
    	                
    	                $html .= "</td><td>";
    	                
    	                $entrySet = false;
    	                
    	                for($e = 0; $e < sizeof($eintraege); $e++) {
    	                    if($eintraege[$e]->getStunde() == $s) {
    	                        
    	                        if($entrySet) $html .= "<hr>";
    	                        
    	                        if($eintraege[$e]->isAusfall()) {
    	                            $html .= "- Entfall (" . $eintraege[$e]->getTeacher() . ")";
    	                            
    	                        }
    	                        else {
    	                            $html .= $eintraege[$e]->getFach() . " bei " . $eintraege[$e]->getTeacher() .":<br />";
    	                            $html .= "Unterrichtsstoff: " . $eintraege[$e]->getStoff() . "<br />";
    	                            $html .= "Hausaufgaben: " .  $eintraege[$e]->getHausaufgabe();
    	                        }
    	                        
    	                        $entrySet = true;
    	                    }
    	                }
    	                
    	                $html .= "</td></tr>";
    	                
    	            }
    	            
    	            eval("\$dayHTML = \"" . DB::getTPL()->get("klassentagebuch/auswertung/pdf/pdf/day") . "\";");
            
	           }
	        
	        }
	        
	        if($dayHTML != "") $pdf->setHTMLContent($dayHTML);
	        
	        $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
	    }
	    
	    $pdf->send();

	    $upload = FileUpload::uploadFromTCPdf('Klassentagebuch - Klasse - ' . $this->klasse . " - von " . $this->startDate . " bis " . $this->endDate. '.pdf', $pdf);
	    
	    return $upload['uploadobject'];
	}
}



?>