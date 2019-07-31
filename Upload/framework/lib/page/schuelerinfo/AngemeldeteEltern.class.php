<?php

class AngemeldeteEltern extends AbstractPage {
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
				"Schülerinfo", "Eltern - Teilnehmer am E-Mail Verfahren"
		) );
		
		$this->checkLogin();
		
		if(!DB::getSession()->isAdmin()) $this->checkAccessWithGroup("Webportal_Elternmail");
	}
	
	public function execute() {
		if($_GET['mode'] == "printAllTeilnehmer") {
			$this->printAllTeilnehmer();
		}
		else if($_GET['mode'] == "printAllNichtTeilnehmer") {
			$this->printAllNichtTeilnehmer();
		}
		else {
			$this->viewStat();
		}
	}
	
	private function printAllTeilnehmer() {
		$klassenData = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler ORDER BY LENGTH(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		$klassen = array();
		while($klasse = DB::getDB()->fetch_array($klassenData)) {
			$klassen[] = $klasse['schuelerKlasse'];
		}
		
		$mpdf=new mPDF('utf-8', 'A4-P');
		
		$mpdf->ignore_invalid_utf8 = true;
		
		
		eval("\$header = \"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/printAllTeilnehmer/index") . "\";");
		
		$header = ($header);
		
		$mpdf->WriteHTML($header,1);
		
		
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			$anzahlSchueler = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "'");
			$anzahlSchueler = $anzahlSchueler[0];
			
			$anzahlEltern = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' AND schuelerAsvID IN (SELECT elternSchuelerAsvID FROM eltern_email)");
			$anzahlEltern = $anzahlEltern[0];
			
			$percent = round($anzahlEltern / $anzahlSchueler * 100);
			
			$angemeldetData = "";
			
			$schueler = DB::getDB()->query("SELECT schuelerName, schuelerRufname, (SELECT group_concat(elternEMail) FROM eltern_email WHERE elternSchuelerAsvID=schueler.schuelerAsvID) as mailList FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' ORDER BY schuelerName ASC, schuelerRufname ASC");
			
			while ($angemeldet = DB::getDB()->fetch_array($schueler)) {
				$angemeldetData .= "<tr><td>" . $angemeldet['schuelerName'] . ", " . $angemeldet['schuelerRufname'] . "</td>";
				if($angemeldet['mailList'] == "") $angemeldet['mailList'] = "<font color=\"red\">Nicht angemeldet</font>";
				else {
					$angemeldet['mailList'] = implode("<br />", explode(",",$angemeldet['mailList']));
				}
				$angemeldetData .= "<td>" . $angemeldet['mailList'] . "</td></tr>";
				
			}
			
			$klassen[$i] = str_replace(" ", "_", $klassen[$i]);
			
			eval("\$klasse = \"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/printAllTeilnehmer/bit") . "\";");
			
			$klasse = ($klasse);
			
			$mpdf->WriteHTML($klasse,2);
			
			if($i != (sizeof($klassen)-1)) $mpdf->AddPage();
			
		}
		
		$mpdf->Output("ElternMail Alle Teilnehmer.pdf",'D');
		exit(0);
		
	}
	
	private function printAllNichtTeilnehmer() {
		$klassenData = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler ORDER BY LENGTH(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		$klassen = array();
		while($klasse = DB::getDB()->fetch_array($klassenData)) {
			$klassen[] = $klasse['schuelerKlasse'];
		}
		
		$mpdf=new mPDF('utf-8', 'A4-P');
		
		$mpdf->ignore_invalid_utf8 = true;
		
		
		eval("\$header = \"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/printAllNichtTeilnehmer/index") . "\";");
		
		$header = ($header);
		
		$mpdf->WriteHTML($header,1);
		
		
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			
			$angemeldetData = "";
			
			$schueler = DB::getDB()->query("SELECT schuelerName, schuelerRufname FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' AND schuelerAsvID NOT IN (SELECT elternSchuelerAsvID FROM  eltern_email) ORDER BY schuelerName ASC, schuelerRufname ASC");
			
			while ($angemeldet = DB::getDB()->fetch_array($schueler)) {
				$angemeldetData .= "<tr><td>" . $angemeldet['schuelerName'] . ", " . $angemeldet['schuelerRufname'] . "</td></tr>";
			}
			
			if($angemeldetData == "") $angemeldetData = "<tr><td><i>Alle Eltern angemeldet!</i></td></tr>";
			
			$klassen[$i] = str_replace(" ", "_", $klassen[$i]);
			
			eval("\$klasse = \"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/printAllNichtTeilnehmer/bit") . "\";");
			
			$klasse = ($klasse);
			
			$mpdf->WriteHTML($klasse,2);
			
			if($i != (sizeof($klassen)-1)) $mpdf->AddPage();
			
		}
		
		$mpdf->Output("ElternMail Alle Teilnehmer.pdf",'D');
		exit(0);
		
	}
	
	private function viewStat() {
		
		$anzahlSchuelerGesamt = 0;
		$angemeldetSchueler = 0;
		
		$klassenData = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler ORDER BY LENGTH(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		$klassen = array();
		while($klasse = DB::getDB()->fetch_array($klassenData)) {
			$klassen[] = $klasse['schuelerKlasse'];
		}
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			$anzahlSchueler = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "'");
			$anzahlSchueler = $anzahlSchueler[0];
			
			$anzahlEltern = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' AND schuelerAsvID IN (SELECT elternSchuelerAsvID FROM eltern_email)");
			$anzahlEltern = $anzahlEltern[0];
			
			$percent = round($anzahlEltern / $anzahlSchueler * 100);
			
			$angemeldetData = "";
			
			$schueler = DB::getDB()->query("SELECT schuelerName, schuelerRufname, (SELECT group_concat(elternEMail) FROM eltern_email WHERE elternSchuelerAsvID=schueler.schuelerAsvID) as mailList FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' ORDER BY schuelerName ASC, schuelerRufname ASC");
			
			while ($angemeldet = DB::getDB()->fetch_array($schueler)) {
				$anzahlSchuelerGesamt++;
				$angemeldetData .= "<tr><td>" . $angemeldet['schuelerName'] . ", " . $angemeldet['schuelerRufname'] . "</td>";
				if($angemeldet['mailList'] == "") $angemeldet['mailList'] = "<font color=\"red\">Nicht angemeldet</font>";
				else {
					$angemeldetSchueler++;
					$angemeldet['mailList'] = implode(", ", explode(",",$angemeldet['mailList']));
				}
				$angemeldetData .= "<td>" . $angemeldet['mailList'] . "</td></tr>";
				
			}
			
			$klassen[$i] = str_replace(" ", "_", $klassen[$i]);
			
			eval("\$list .= \"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/bit") . "\";");
		}
		
		$percentAngemeldet = round($angemeldetSchueler / $anzahlSchuelerGesamt * 100);
		
		eval("echo(\"" . DB::getTPL()->get("elternmail/elternmailteilnehmer/index") . "\");");
	}
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Elternmail: Anzeige der Teilnehmer';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
		
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>