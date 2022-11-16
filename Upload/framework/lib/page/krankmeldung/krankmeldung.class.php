<?php




class krankmeldung extends AbstractPage {
	
	/**
	 * Schüler, die der Benutzer krank melden darf.
	 * @var schueler[]
	 */
	private $schueler = [];
		
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
			"Krankmeldung" 
		) );
		

		$this->checkLogin();
		
		$accessOK = false;
		
		if(DB::getSession()->isEltern()) {
			$this->schueler = DB::getSession()->getElternObject()->getMySchueler();
			$accessOK = true;
		}
		
		if(DB::getSession()->isAdmin()) {
			$this->schueler = schueler::getAll('length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC');
			$accessOK = true;
		}
		
		if(DB::getSession()->isPupil()) {
			if(DB::getSettings()->getBoolean("krankmeldung-volljaehrige-schueler")) {
				$alter = DB::getSession()->getPupilObject()->getAlter();
				if($alter < 18 && !DB::getSettings()->getBoolean("krankmeldung-schueler")) {
					new errorPage("Schüler noch nicht volljährig!");
				}
				else {
					$this->schueler = [DB::getSession()->getPupilObject()];
					$accessOK = true;
				}
			}
			
			if(DB::getSettings()->getBoolean("krankmeldung-schueler")) {
				$this->schueler = [DB::getSession()->getPupilObject()];
			}
		}
		
		
		if(!$accessOK) {
			new errorPage();
		}
	}
	
	public function execute() {		
		// Meine Krankmeldungen
		
		if($_GET['mode'] == "saveKrankmeldung") {
			$kinder = $this->schueler;
			
			/**
			 * 
			 * @var schueler $kind
			 */
			$kind = null;
			
			for($i = 0; $i < sizeof($kinder); $i++) {
				if($_POST['schuelerID'] == $kinder[$i]->getID()) {
					$kind = $kinder[$i];
				}
			}
			
			if($kind == null) new errorPage("Das angegebene Kind existiert nicht!");
			
			$today = DateFunctions::getTodayAsNaturalDate();
			while(DateFunctions::getWeekDayFromNaturalDate($today) == 0 || DateFunctions::getWeekDayFromNaturalDate($today) == 6 || ferien::isFerien(DateFunctions::getMySQLDateFromNaturalDate($today)))
				$today = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
			
			if($today != DateFunctions::getTodayAsNaturalDate()) {
				$skipNextDay = true;
			}
			
			$nextDay = null;
			
			if(!$skipNextDay) {
				$nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
			
				while(DateFunctions::getWeekDayFromNaturalDate($nextDay) == 0 || DateFunctions::getWeekDayFromNaturalDate($nextDay) == 6)
					$nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($nextDay)));
			}
			
			$_POST['krankAb'] = null;
			
			// Debugger::debugObject($_REQUEST,1);
			
			if($_POST['fromDate'] == 'today') {
				$_POST['krankAb'] = $today;
			}
			
			if($_POST['fromDate'] == 'tomorrow' && $nextDay != null) {
				$_POST['krankAb'] = $nextDay;
			}
			
			if($_POST['krankAb'] != null) {
				if($_POST['anzahlTage'] <= DB::getSettings()->getValue("krankmeldung-anzahl-tage")) {
					if($_POST['anzahlTage'] == 1) $_POST['krankBis'] = $_POST['krankAb'];
					
					else
						$_POST['krankBis'] = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addDaysToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($_POST['krankAb']),$_POST['anzahlTage']-1));
				}
				else {
					$_POST['krankBis'] = null;
				}
			}
			
		
			
			if(DateFunctions::isNaturalDate($_POST['krankAb']) && DateFunctions::isNaturalDate($_POST['krankBis'])) {
				if(!DateFunctions::isNaturalDateTodayOrLater($_POST['krankAb'])) {
					$this->showNewKrankmeldungForm("Die Krankmeldung kann nur für ein Datum ab heute abgegeben werden.");
					exit(0);
				}
				
				if(!DateFunctions::isNaturalDateAfterAnother($_POST['krankBis'], $_POST['krankAb'])) {
					$this->showNewKrankmeldungForm("Das Ende der Krankmeldung muss nach dem Start liegen.");
					exit(0);
				}

                if(DB::getSession()->isPupil()) {
					if(DB::getSession()->getPupilObject()->getAlter() >= 18) {
						$_POST['krankmeldungDurch'] = 'schuelerue18';
					}
					else {
						$_POST['krankmeldungDurch'] = 'schueleru18';
					}
				}
				
				
				
				$hasTermine = false;
				
				if(DB::getSettings()->getBoolean("krankmeldung-hinweis-lnw") && stundenplandata::getCurrentStundenplan() != null) {
				
					
					$lnws = Leistungsnachweis::getByClass([$kind->getKlasse()], DateFunctions::getMySQLDateFromNaturalDate($_POST['krankAb']), DateFunctions::getMySQLDateFromNaturalDate($_POST['krankBis']));
					
					for($i = 0; $i < sizeof($lnws); $i++) {
						if($lnws[$i]->showForNotTeacher()) {
							$hasTermine = true;
							$terminListe .= $lnws[$i]->getKlasse() . ": " . $lnws[$i]->getArtLangtext() . " in " . $lnws[$i]->getFach() . " bei " . $lnws[$i]->getLehrer() . "<br />";
						}
					}
				}

                $krankmeldungDurch = DB::getDB()->escapeString($_POST['krankmeldungDurch']);
                if (!$krankmeldungDurch) {
                    $krankmeldungDurch = 's';
                }

				DB::getDB()->query("INSERT INTO absenzen_krankmeldungen (
						krankmeldungSchuelerASVID,
						krankmeldungDate,
						krankmeldungUntilDate,
						krankmeldungElternID,
						krankmeldungDurch,
						krankmeldungKommentar, krankmeldungTime)
						values(
						'" . $kind->getAsvID() . "',
						'" . DateFunctions::getMySQLDateFromNaturalDate($_POST['krankAb']) . "',
						'" . DateFunctions::getMySQLDateFromNaturalDate($_POST['krankBis']) . "',
						'" . DB::getUserID() . "',
						'" . $krankmeldungDurch . "',
						'" . DB::getDB()->escapeString($_POST['krankmeldungKommentar']) . "',UNIX_TIMESTAMP())");
				
				
				$newID = DB::getDB()->insert_id();
				
				$showHinweisDownloadEntschuldigung = true;
				if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen')) {
				    $showHinweisDownloadEntschuldigung = false;
				}
				
				eval("echo(\"" . DB::getTPL()->get("krankmeldung/ok") . "\");");
				PAGE::kill(true);
     	 //exit(0);
			}
			else {
				$this->showNewKrankmeldungForm("Die Datumsangaben sind nicht korrekt!");
			}
		}
		
		else if($_GET['mode'] == 'deleteKrankmeldung') {
			$this->deleteKrankmeldung();
		}
		else if($_GET['mode'] == 'downloadEntschuldigung') {
		    
		    $krankmeldung = DB::getDB()->query_first("SELECT * FROM absenzen_krankmeldungen WHERE krankmeldungElternID='" . DB::getSession()->getUserID() . "' AND krankmeldungID='" . intval($_REQUEST['krankmeldungID']) . "'");
		    
		    if($krankmeldung['krankmeldungID'] > 0) {
		      
		        $generator = new AbsenzEntschuldigungGenerator();
		        $generator->addKrankmeldung($krankmeldung);
		        $generator->send();
		    
		    }
		    else {
		        new errorPage("Zugriffsverletzung");
		    }
		}
		
		else {
			// Neue Krankmeldung
			$this->showNewKrankmeldungForm();
		}
		
		
		// Neue Krankmeldung einreichen
		
	}
	
	private function deleteKrankmeldung() {
		$krankmeldungen = DB::getDB()->query("SELECT * FROM absenzen_krankmeldungen JOIN schueler on absenzen_krankmeldungen.krankmeldungSchuelerASVID=schueler.schuelerAsvID WHERE krankmeldungSchuelerASVID IN (SELECT elternSchuelerAsvID FROM eltern_email WHERE elternUserID = '" . DB::getSession()->getUserID() . "') AND krankmeldungAbsenzID=0");
		
		$offeneKrankmeldungen = "";
		
		while($km = DB::getDB()->fetch_array($krankmeldungen)) {
			if($_GET['krankmeldungID'] == $km['krankmeldungID']) {
				DB::getDB()->query("DELETE FROM absenzen_krankmeldungen WHERE krankmeldungID='" . $km['krankmeldungID'] . "'");
				break;
			}
		}
		
		eval("echo(\"" . DB::getTPL()->get("krankmeldung/deleteok") . "\");");
		PAGE::kill(true);
    //exit(0);
		
	}
	
	private function showNewKrankmeldungForm($errorMessage="") {
		// Meine Kinder:
		
		$kinder = $this->schueler;
		
		if(sizeof($kinder) > 1) $infoOtherSchueler = "<b>Achtung: Sie haben hier " . sizeof($kinder) . " Schüler zur Auswahl!</b>";
		else $infoOtherSchueler = "";
		
		$selectFields = "";
		for($i = 0; $i < sizeof($kinder); $i++) {
			$selectFields .= "<option value=\"". $kinder[$i]->getID() . "\">" . $kinder[$i]->getCompleteSchuelerName() . " (Klasse " . $kinder[$i]->getKLasse() . ")</option>\r\n";
		}
		
		$today = DateFunctions::getTodayAsNaturalDate();
		while(DateFunctions::getWeekDayFromNaturalDate($today) == 0 || DateFunctions::getWeekDayFromNaturalDate($today) == 6 || ferien::isFerien(DateFunctions::getMySQLDateFromNaturalDate($today)))
			$today = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
		
		$beschriftungHeute = "<b>Heute</b> (" . functions::getDayName(DateFunctions::getWeekDayFromNaturalDate($today)-1) . ", " . $today . ")";
		if($today != DateFunctions::getTodayAsNaturalDate()) {
			$beschriftungHeute = "Nächster Schultag (" . functions::getDayName(DateFunctions::getWeekDayFromNaturalDate($today)-1) . ", " . $today . ")";
			$skipNextDay = true;
		}
		
		$nextDay = null;
		
		if(!$skipNextDay) {
			$nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
		
			while(DateFunctions::getWeekDayFromNaturalDate($nextDay) == 0 || DateFunctions::getWeekDayFromNaturalDate($nextDay) == 6 || Ferien::isFerien(DateFunctions::getMySQLDateFromNaturalDate($nextDay)) != null) {
				$nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($nextDay)));
			}
			
			$beschriftungMorgen = "Nächster Schultag (" . functions::getDayName(DateFunctions::getWeekDayFromNaturalDate($nextDay)-1) . ", " . $nextDay . ")";
		}
		
		$selectNumDaysToday = "";
		$selectNumDaysNextDay = "";
		
		for($i = 1; $i <= DB::getSettings()->getValue("krankmeldung-anzahl-tage"); $i++) {
			$selectNumDays .= "<label><input type=\"radio\" name=\"anzahlTage\" value=\"$i\"" . (($i == 1) ? " checked=\"checked\"" : "") . "> $i " . (($i > 1) ? "Tage" : "Tag") . "</label><br />";
		}
				
		if($errorMessage != "") $errorMessage = "<div class=\"alert alert-danger\"><h4><i class=\"icon fa fa-ban\"></i> Fehler bei der Krankmeldung</h4>
                $errorMessage
              </div>";
		
                
        // Offene Krankmeldungen laden
        
		$krankmeldungen = DB::getDB()->query("SELECT * FROM absenzen_krankmeldungen JOIN schueler on absenzen_krankmeldungen.krankmeldungSchuelerASVID=schueler.schuelerAsvID WHERE krankmeldungSchuelerASVID IN (SELECT elternSchuelerAsvID FROM eltern_email WHERE elternUserID = '" . DB::getSession()->getUserID() . "') AND krankmeldungAbsenzID=0");
		
		$offeneKrankmeldungen = "";
		
		while($km = DB::getDB()->fetch_array($krankmeldungen)) {
			$kindName = $km['schuelerRufname'] . " " . $km['schuelerName'];
			$krankAb = functions::getFormatedDateWithDayFromSQLDate($km['krankmeldungDate']);
			$krankBis = functions::getFormatedDateWithDayFromSQLDate($km['krankmeldungUntilDate']);
			
			$meldung = "";
			switch($km['krankmeldungDurch']) {
				case "v": $meldung = "Vater";break;
				case "m": $meldung = "Mutter";break;
				case "s": $meldung = "Sonstige";break;
				case "schueleru18":
					case "schuelerue18":
						$meldung = "Schüler";break;
			}
			
			$kommentar = @htmlspecialchars(($km['krankmeldungKommentar']));
			
			eval("\$offeneKrankmeldungen .= \"" . DB::getTPL()->get("krankmeldung/offenbit") . "\";");
		}
		
		
		if($offeneKrankmeldungen == "") $offeneKrankmeldungen = "<tr><td colspan=\"6\" style=\"text-align:center\"><i class=\"fa fa-ban\"></i> Keine offenen Krankmeldungen vorhanden. Wenden Sie sich bitte für Änderungen direkt an das Sekretariat!</td>";
		
		
				
		eval("echo(\"" . DB::getTPL()->get("krankmeldung/new") . "\");");
		PAGE::kill(true);
    //exit(0);
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "krankmeldung-volljaehrige-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Krankmeldung durch volljährige Schüler aktivieren",
				'text' => "Ist diese Einstellung aktiv, können sich volljährige Schüler selbst krank melden."
			],
			[
				'name' => "krankmeldung-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Krankmeldung durch Schüler aktivieren",
				'text' => "Ist diese Einstellung aktiv, können sich Schüler selbst krank melden. (Auch die unter 18 Jahren!)"
			],
			[
				'name' => "krankmeldung-anzahl-tage",
				'typ' => 'NUMMER',
				'titel' => "Maximale Anzahl der Tage einer Krankmeldung",
				'text' => "Die Anzahl der Tage legt fest, wie viele Tage eine Online Krankmeldung maximal umfassen darf."
			],
		    [
		        'name' => "krankmeldung-hinweis-lnw",
		        'typ' => 'BOOLEAN',
		        'titel' => "Hinweis auf Attestpflicht bei angekkündigten LNW?",
		        'text' => "Bei aktivierter Option erhalten die Eltern einen Hinweis, dass ein Attest benötigt wird, wenn da an dem Tag ein angekündigter Leistungsnachweis im Klassenkalender eingetragen ist."
		    ],
		    [
		        'name' => "krankmeldung-bemerkung-abschalten",
		        'typ' => 'BOOLEAN',
		        'titel' => "Bemerkungsfeld abschalten?",
		        'text' => "Bei aktivierter Option ist das Eingaben einer Bemerkung zur Krankmeldung nicht mehr möglich. Dieser Zustand wird aus Datenschutzgründen empfohlen, da die Eltern hier oft Arten von Krankheiten angeben, die nicht erfasst werden dürfen."
	         ]
		   
	     ];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Online Krankmeldung';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
	public static function hasAdmin() {
		return true;
	}

	public static function getAdminMenuGroup() {
		return "Absenzenverwaltung";
	}

	public static function dependsPage() {
		return ['absenzensekretariat', 'absenzenberichte','absenzenstatistik'];
	}
	
	public static function userHasAccess($user) {
	
		if(DB::getSession()->isEltern()) {
			return true;
		}
		
		if(DB::getSession()->isAdmin()) {
			return true;
		}
		
		if(DB::getSession()->isPupil()) {
			
			if(DB::getSettings()->getBoolean("krankmeldung-schueler")) {
				return true;
			}
			
			if(DB::getSettings()->getBoolean("krankmeldung-volljaehrige-schueler")) {
				$alter = DB::getSession()->getPupilObject()->getAlter();
				return $alter >= 18;
			}
			

		}
		
		return false;
	}

}

?>