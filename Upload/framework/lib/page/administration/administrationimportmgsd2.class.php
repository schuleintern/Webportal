<?php


class administrationimportmgsd2 extends AbstractPage {

	private static $info;
	
	private static $errorMessage = "";
	
	private static $showMessage = "";
	
	public function __construct() {
		parent::__construct(array("Administration", "Import von MGSD2 Daten"));
		
		$this->checkLogin();
		
		new errorPage();
		
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuGroup() {
		return 'Im-/Export';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file-p';
	}
	
	public static function displayAdministration($selfURL) {
			switch($_GET['action']) {
			default:
				return self::index();
			break;
			
			case "doupload":
				return self::doupload();
			break;
			
			case "deleteImport":
				return self::deleteImport();
			break;
		}
	}
	public function execute() {}
	
	private static function deleteImport() {
		DB::getDB()->query("DELETE FROM absenzen_befreiungen WHERE befreiungID IN (SELECT absenzBefreiungID FROM absenzen_absenzen WHERE absenzBemerkung LIKE 'MGSD Import%')");
		DB::getDB()->query("DELETE FROM absenzen_beurlaubungen WHERE beurlaubungID IN (SELECT absenzBeurlaubungID FROM absenzen_absenzen WHERE absenzBemerkung LIKE 'MGSD Import%')");
		DB::getDB()->query("DELETE FROM absenzen_verspaetungen WHERE verspaetungKommentar LIKE 'MGSD Import%'");
		DB::getDB()->query("DELETE FROM absenzen_absenzen WHERE absenzBemerkung LIKE 'MGSD Import%'");
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("administration/importmgsd2/deleteOK") . "\";");
		
		return $html;
		
	}
	
	private static function doupload() {
		if($_FILES['importFile']['tmp_name'] != "") {			
			$data = file($_FILES['importFile']['tmp_name'], FILE_IGNORE_NEW_LINES);
			
			// Testen, ob richtige Datenbank exportiert wurde.
			
			$success = false;
			for($i = 0; $i < sizeof($data); $i++) {
				if($data[$i] == "CREATE SCHEMA mgsd_sabs2;") {
					$success = true;
					break;
				}
			}
			
			if(!$success) {
				self::$errorMessage = "Der angegebene Export enthält keine Tabelle mit dem Namen \"mgsd_sabs2\"";
				return self::index();
				exit(0);
			}
			
			
			// Absenzen Daten roh zusammenstellen
			// Startzeile suchen
			
			$absenzenData = array();
			
			$firstLine = -1;
			$lastLine = -1;
			
			$startFound = false;
			for($i = 0; $i < sizeof($data); $i++) {
				if(strpos($data[$i], "datum_krank, datum_bis")) {
					$firstLine = $i+1;
					$startFound = true;
					break;
				}
			}
			
			if(!$startFound) {
				self::$rrorMessage = "Der angegebene Export enthält keine Absenzentabelle!";
				return self::index();
			}
			
			// Ende suchen
			
			$found = false;
			for($i = $firstLine; $i < sizeof($data); $i++) {
				if($data[$i] == "\\.") {
					$lastLine = $i-1;
					$found = true;
					break;
				}
			}
			
			if(!$found) {
				self::$errorMessage = "Der angegebene Export ist beschädigt!";
				return self::$index();
			}
			
			for($i = $firstLine; $i <= $lastLine; $i++) {
				$absenzenData[] = explode("\t",$data[$i]);
			}
			
			$absenzenDataReal = array();
			
			$maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
			
			for($i = 0; $i < sizeof($absenzenData); $i++) {
				$quelle = "TELEFON";
				
				if($absenzenData[$i][19] == 'ESIS') {
					$quelle = "WEBPORTAL";
				}
				
				$timedata = explode(" ",$absenzenData[$i][17]);
				$timedata2 = explode(".",$timedata[1]);
				$timedata3 = explode(":",$timedata2[0]);
				$timedata4 = explode("-",$timedata[0]);
				
				$time = mktime($timedata3[0],$timedata3[1],$timedata3[2],$timedata4[1],$timedata4[2],$timedata4[0]);
				
				$stunden = array();
				
				$stunde = 1;
				$schriftlich = 0;
				for($s = 5; $s <= 16; $s++) {
					if($stunde <= $maxStunden) {
						if($absenzenData[$i][$s] == "t") {
							$stunden[] = $stunde;
						}
						if($absenzenData[$i][$s] == 's') {
							$stunden[] = $stunde;
							$schriftlich = 1;
						}
					}
					else break;
					$stunde++;
				}
				
				$befreiungID = 0;
				if($absenzenData[$i][3] == 3) {
					DB::getDB()->query("INSERT INTO absenzen_befreiungen (befreiungUhrzeit,befreiungLehrer,befreiungPrinted) values(
						'" . DB::getDB()->escapeString($timedata3[0]) . ":" . DB::getDB()->escapeString($timedata3[1]) . "',
						'MGSD',
						1
					)");
					$befreiungID = DB::getDB()->insert_id();
				}
				
				$beurlaubungID = 0;
				if($absenzenData[$i][3] == 2) {
					DB::getDB()->query("INSERT INTO absenzen_beurlaubungen (beurlaubungCreatorID,beurlaubungPrinted,beurlaubungIsInternAbwesend) values(
						'" . DB::getUserID() . "',
						1,
						0
					)");
					$beurlaubungID = DB::getDB()->insert_id();
				}
				
				$absenzenDataReal = array(
						"NULL",
				    "'" . DB::getDB()->escapeString($absenzenData[$i][21]) . "'",
				    "'" . DB::getDB()->escapeString(explode(" ",$absenzenData[$i][1])[0]) . "'",
				    "'" . DB::getDB()->escapeString(explode(" ",$absenzenData[$i][2])[0]) . "'",
				    "'" . DB::getDB()->escapeString($quelle) . "'",
				    "'" . DB::getDB()->escapeString("MGSD Import\r\n" . trim(($absenzenData[$i][4]))) . "'",
				    "'" . DB::getDB()->escapeString($time) . "'",
				    "'" . DB::getDB()->escapeString(DB::getUserID()) . "'",
				    "'" . DB::getDB()->escapeString($befreiungID) . "'",
				    "'" . DB::getDB()->escapeString($beurlaubungID) . "'",
				    "'" . DB::getDB()->escapeString(implode(",",$stunden)) . "'",
						"'1'",
				    "'" . DB::getDB()->escapeString($schriftlich) . "'",
						"'0'"
						
				);
				
				
				
				DB::getDB()->query("INSERT INTO absenzen_absenzen VALUES(
					" . implode(",",$absenzenDataReal) . "	
					)
						
				");
			}
			
			$result = "Es wurden " . sizeof($absenzenData) . " Absenzen importiert.\r\n";
			
			// Verspätungen
			
			$startFound = false;
			$firstLine = -1;
			$lastLine = -1;
			for($i = 0; $i < sizeof($data); $i++) {
				if(substr($data[$i],0,19) == "COPY \"Verspaetungs\""){
					$firstLine = $i+1;
					$startFound = true;
					break;
				}
			}
			
			// Ende suchen
				
			$endFound = false;
			for($i = $firstLine; $i < sizeof($data); $i++) {
				if($data[$i] == "\\.") {
					$lastLine = $i-1;
					$endFound = true;
					break;
				}
			}
			
			$anzahlVerspaetungen = 0;			

			$dataVerspaetung = array();
			
			if($startFound && $endFound) {
				for($i = $firstLine; $i <= $lastLine; $i++) {
					$dataLine = explode("\t",$data[$i]);
					$minuten = $dataLine[2];
					$schueler = $dataLine[6];
					$kommentar = "MGSD Import\r\n" . trim(($dataLine[4]));
					$date = explode(" ",$dataLine[5]);
					$date = $date[0];
					
					DB::getDB()->query("INSERT INTO absenzen_verspaetungen (verspaetungSchuelerAsvID, verspaetungDate, verspaetungMinuten, verspaetungKommentar) 
							values(
								'" . $schueler . "',
								'" . $date . "',
								'" . $minuten . "',
								'" . DB::getDB()->escapeString($kommentar) . "'
							)");
					$anzahlVerspaetungen++;
				}
				
				
			}
			
			
			$result .= "Es wurden " . $anzahlVerspaetungen . " Verspätungen importiert.";
			
			eval("\$html = \"" . DB::getTPL()->get("administration/importmgsd2/ok") . "\";");
			return $html;
			
		}
		else {
			self::$errorMessage = "Der Upload war leider nicht erfolgreich!";
			return self::index();
		}
	}
	
	private static function index() {
		
		$absenz = DB::getDB()->query_first("SELECT * FROM absenzen_absenzen WHERE absenzBemerkung LIKE 'MGSD Import%' LIMIT 1");
		if($absenz['absenzID'] > 0) $isImported = true;
		else $isImported = false;
		
		// Formular anzeigen
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("administration/importmgsd2/index") . "\";");
		
	
		return $html;
	}
	
	private function settings() {
		
	}
	
	public static function hasSettings() {
		return false;
	}
	
	
	public static function getSiteDisplayName() {
		return 'MGSD2 Import';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array(
				array(
						'groupName' => 'Webportal_Administrator',
						'beschreibung' => 'Administrator der ganzen SchuleIntern Instanz. (Sollte nur ein sehr kleiner Kreis von Personen sein.)'
				)
		);
	
	}
	public static function getSettingsDescription() {
		return array(
			
		);
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
	
}


?>