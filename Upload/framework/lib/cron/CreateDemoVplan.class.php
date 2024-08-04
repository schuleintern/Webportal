<?php

/**
 * Erstellt in der Demo Version einen fiktiven Vertretungsplan
 * @author Christian
 *
 */


class CreateDemoVplan extends AbstractCron {

	private $createdVPlans= "";
	
	public function __construct() {
	}

	public function execute() {
		if(!DB::isSchulnummern(9400)) {
			$heute = DateFunctions::getTodayAsSQLDate();
			
			while(true) {
				$addDay = false;
				if(Ferien::isFerien($heute)) {
					$addDay = true;
				}
				
				if(DateFunctions::isSQLDateWeekEnd($heute)) $addDay = true;
				
				if($addDay) $heute = DateFunctions::addOneDayToMySqlDate($heute);
				else break;
			}
			
			$morgen = DateFunctions::addOneDayToMySqlDate($heute);
			
			while(true) {
				$addDay = false;
				if(Ferien::isFerien($morgen)) {
					$addDay = true;
				}
				
				if(DateFunctions::isSQLDateWeekEnd($morgen)) $addDay = true;
				
				if($addDay) $morgen= DateFunctions::addOneDayToMySqlDate($morgen);
				else break;
			}
			
			
			$this->createdVPlans .= "Heute erstellt. Datum: " . DateFunctions::getNaturalDateFromMySQLDate($heute) . "\r\n";
			$this->createdVPlans .= "Morgen erstellt. Datum: " . DateFunctions::getNaturalDateFromMySQLDate($morgen);
			
			echo($heute . "<br />");
			echo($morgen);
			
			
			$this->setRandomLehrerVplan('lehrerheute', $heute);
			$this->setRandomLehrerVplan('lehrermorgen', $morgen);
			
			$this->setRandomSchuelerVplan('schuelerheute', $heute);
			$this->setRandomSchuelerVplan('schuelermorgen', $morgen);
			
			
		}
	}
	
	private function setRandomSchuelerVplan($plan,$sqldate) {
		$alleKlassen = klasse::getAllKlassen();
		
		$wochentag = DateFunctions::getWeekDayFromSQLDate($sqldate);
		
		$stundenplan = stundenplandata::getStundenplanAtDate($sqldate);
		
		$content = '<table class="mon_list" >' . "\r\n" . '<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>' . "\r\n";
				
		for($i = 0; $i < sizeof($alleKlassen); $i++) {
			// $klassen = $stundenplan->getAllGradesForTeacher($alleKlassen[$i]->getKuerzel());
			
			$klasse = $alleKlassen[$i]->getKlassenName();
			
			
			$content .= '<tr class="list even"><td class="list" align="center">' . $klasse . '</td><td class="list" align="center">4</td><td class="list" align="center">---*</td><td class="list" align="center">Englisch</td><td class="list" align="center">R-E3</td><td class="list" align="center">Vertretung</td><td class="list" align="center">Englisch</td><td class="list" align="center">---*</td><td class="list" align="center">&nbsp;</td><td class="list" align="center">&nbsp;</td></tr>';
			
			$content .= "\r\n";
		}
		
		$content .= "</table>";
		
		DB::getDB()->query("UPDATE vplan SET
			vplanDate='" . DB::getDB()->escapeString(DateFunctions::getWeekDayNameFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($sqldate)) . ", " . DateFunctions::getNaturalDateFromMySQLDate($sqldate)) . "',
			vplanContent = '" . DB::getDB()->escapeString($content) . "',
			vplanUpdate ='" . date("d.m.Y H:i") . "',vplanInfo='',vplanContentUncensored = '" . DB::getDB()->escapeString($content) . "' WHERE vplanName='" . $plan . "'");
	}
	
	private function setRandomLehrerVplan($plan,$sqldate) {
		$alleLehrer = lehrer::getAll();
		
		$wochentag = DateFunctions::getWeekDayFromSQLDate($sqldate);
		
		$stundenplan = stundenplandata::getStundenplanAtDate($sqldate);
		
		$content = '<table class="mon_list">' . "\r\n" . '<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>' . "\r\n" . '';
		
		for($i = 0; $i < sizeof($alleLehrer); $i++) {
			$klassen = $stundenplan->getAllGradesForTeacher($alleLehrer[$i]->getKuerzel());
			
			$kuerzel = $alleLehrer[$i]->getKuerzel();
			
			$content .= "<tr class='list even'><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\">" . $kuerzel . "</td>";
			$content .= "<td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\">4</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\">";
			
			$content .= $klassen[1];			
			
			$content .= "</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\">Englisch</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\" >R-6Mu</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\" >Vertretung</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\" >Englisch</td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\" ><strike>XYZ</strike></td><td class=\"list\" align=\"center\" style=\"background-color: #80FFFF\" >";
			$content .= '&nbsp;</td><td class="list" align="center" style="background-color: #80FFFF" >&nbsp;</td></tr>' . "\r\n";		//NEW LINE!!
		}
		
		$content .= "</table>";
		
		DB::getDB()->query("UPDATE vplan SET
			vplanDate='" . DB::getDB()->escapeString(DateFunctions::getWeekDayNameFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($sqldate)) . ", " . DateFunctions::getNaturalDateFromMySQLDate($sqldate)) . "',
			vplanContent = '" . DB::getDB()->escapeString($content) . "',
			vplanUpdate ='" . date("d.m.Y H:i") . "',vplanInfo='',vplanContentUncensored = '" . DB::getDB()->escapeString($content) . "' WHERE vplanName='" . $plan . "'");
		
	}
	
	public function getName() {
		return "Demo Vertretungsplan erstellen";
	}
	
	public function getDescription() {
		return "Erstellt in der Demo Version einen fiktiven Vertretungsplan";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
	    if(DB::isSchulnummern(9400)) {
	    	return [
	    		'success' => true,
	    		'resultText' => $this->createdVPlans
	    	];
	    }
	    else {
	    	return [
	    			'success' => true,
	    			'resultText' => 'Installation ist keine Demoversion.'
	    	];
	    }
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 21600;		// Zwei mal am Tag ausführen
	}
}



?>