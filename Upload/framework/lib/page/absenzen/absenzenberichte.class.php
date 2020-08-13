<?php

class absenzenberichte extends AbstractPage {

	private $stundenplan = null;
	private $stundenplanActiveKlasse = null;
	
	private $isSekretariat = false;
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct(array("Absenzenverwaltung", "Berichte"));
		
		$this->checkLogin();
	
		$this->isSekretariat = DB::getSession()->isMember("Webportal_Absenzen_Sekretariat");
		
		if(DB::getSession()->isAdmin()) $this->isSekretariat = true;
		
		$this->stundenplan = stundenplandata::getCurrentStundenplan();

	}

	public function execute() {
	
		include_once("../framework/lib/data/absenzen/Absenz.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");
		
		switch($_GET['mode']) {
			default:
				$this->showIndex();
			break;
			
			case 'day':
				// Tagesbericht
				if($this->isSekretariat) $this->tagesBericht();
				else new errorPage("Leider ist dieser Bericht nicht für Sie verfügbar!");
			break;
			
			case 'fehlendeSchriftlicheEntschuldigungen':
				$this->fehlendeSchriftlicheEntschuldigungen();
			break;
			
			case 'gradeStat':
				$this->gradeStat();
			break;
			
			case "notenmanagerExport":
				$this->notenmanagerExport();
			break;
			
			case 'schuelerbericht':
				$this->schuelerBericht();
			break;
			
			case 'dayStatus':
				$this->dayStatus();
			break;
			
			case 'dayStatusOnlyAbsent':
			    $this->dayStatus(true);
			break;
		}
	}
	
	private function dayStatus($onlyShowAbsenzen=false) {
		$date = $_GET['datum'];
		
		if(!DateFunctions::isNaturalDate($date)) {
			$date = DateFunctions::getTodayAsSQLDate();
		}
		
		$klassen = klasse::getAllKlassen();
		
		
		
		$print = new PrintNormalPageA4WithHeader('Tagesliste');
		$print->showHeaderOnEachPage();
		
		$print->setPrintedDateInFooter();
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			$schueler = $klassen[$i]->getSchueler(false);
			
			$klasseHTML = '';
			
			$klassenLeitung = '';
			
			$hasAbsenz = false;
			
			$klS = $klassen[$i]->getKlassenLeitung();
			
			for($o = 0; $o < sizeof($klS); $o++) {
				$klassenLeitung .= (($o > 0) ? (", ") : (""));
				$klassenLeitung .= $klS[$o]->getDisplayNameMitAmtsbezeichnung();
			}
			
			if($onlyShowAbsenzen) {
			    $klasseHTML .= "<h1>Absenzen der Klasse " . $klassen[$i]->getKlassenName() . "</h1><h2>Stand: " . DateFunctions::getNaturalDateFromMySQLDate($date) . " - " . date("H:i")  . " Uhr</h2>Klassenleitung: " . $klassenLeitung . "<br />\r\n";
			}
			else $klasseHTML .= "<h1>Klasse " . $klassen[$i]->getKlassenName() . "</h1><h2>Stand: " . DateFunctions::getNaturalDateFromMySQLDate($date) . " - " . date("H:i")  . " Uhr</h2>Klassenleitung: " . $klassenLeitung . "<br />\r\n";
			
			
			$klasseHTML .= "<table border=\"1\" style=\"width:100%\" cellpadding=\"2\">";
			$klasseHTML .= "<tr><th style=\"width:10%\">#</th><th style=\"width:40%\">Name</th><th style=\"width:50%\">Absenzen</th></tr>\r\n";
			
			$absenzen = Absenz::getAbsenzenForDate($date, $klassen[$i]->getKlassenName());
			
			for($s = 0; $s < sizeof($schueler); $s++) {
				$klasseHTML .= "<tr><td>" . ($s+1) . "</td><td>" . $schueler[$s]->getCompleteSchuelerName() . "</td><td>";
				
				// Suche Absenz
				
				$has = false;
				for($a = 0; $a < sizeof($absenzen); $a++) {
					if($absenzen[$a]->getSchueler()->getAsvID() == $schueler[$s]->getAsvID()) {
						if($has) $klasseHTML .= "<br />";
						
						$klasseHTML .= "Absenz - Stunden: " . $absenzen[$a]->getStundenAsString();
						$klasseHTML .= (($absenzen[$a]->isBefreiung()) ? " - Befreiung" : "");
						$klasseHTML .= (($absenzen[$a]->isBeurlaubung()) ? " - Beurlaubung" : "");
						$has = true;
						$hasAbsenz = true;
					}
				}
				
				$klasseHTML .= "</td>";
				
				$klasseHTML .= "</tr>";
			}
			
			
			$klasseHTML .= "</table>";
			
			if(($onlyShowAbsenzen && $hasAbsenz) || !$onlyShowAbsenzen) {
			    $print->setHTMLContent($klasseHTML);
			}
			
			
			
		}
		
		$print->send();
	}
	
	private function schuelerBericht() {
		
		$schueler = schueler::getByAsvID($_GET['schuelerAsvID']);
		
		if($schueler == null) new errorPage("Der angegebene Schüler existiert nicht!");
		
		$access = false;
		
		if($this->isSekretariat) $access = true;
		else if(!DB::getSession()->isTeacher()) $access = false;
		else {
			$klasse = $schueler->getKlassenObjekt();
			if($klasse->isKlassenLeitung(DB::getSession()->getTeacherObject())) $access = true;
		}
		
		
		if($access) {

			
			$date = $_GET['currentDate'];
			$grade = $schueler->getKlasse();
			
			$absenzen = Absenz::getAbsenzenForSchueler($schueler);
			
			$absenzenCalculator = new AbsenzenCalculator($absenzen);
			$absenzenCalculator->calculate();
			
			$absenzenStat = $absenzenCalculator->getDayStat();
			
			$total = $absenzenCalculator->getTotal();
			$beurlaubt = $absenzenCalculator->getBeurlaubt();
			$entschuldigt = $absenzenCalculator->getEntschuldigt();
			$fpatotal = $absenzenCalculator->getFPATotal();
			
			
			$krankenzimmer = DB::getDB()->query("SELECT * FROM absenzen_sanizimmer WHERE sanizimmerSchuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY sanizimmerTimeStart");
			
			$sanizimmerTotal = 0;
			$sanizimmerMinutenTotal = 0;
			
			$sanizimmerData = array();
			while($s = DB::getDB()->fetch_array($krankenzimmer)) {
				$sanizimmerTotal++;
				$sanizimmerMinutenTotal += floor(($s['sanizimmerTimeEnde'] - $s['sanizimmerTimeStart']) / 60);
				$sanizimmerData[] = $s;
			}
			
			
			$verspaetungenTotal = 0;
			$verspaetungenMinutenTotal = 0;
			
			$krankenzimmer = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY verspaetungDate");
			
			$verspaetungData = array();
			while($s = DB::getDB()->fetch_array($krankenzimmer)) {
				$verspaetungenTotal++;
				$verspaetungenMinutenTotal += $s['verspaetungMinuten'];
				$verspaetungData[] = $s;
			}
			
			$stundenHeader = "";
			
			$width = 40;
			
			
			$maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
			
			$percell = $width / $maxStunden;
			
			for($i = 1; $i <= $maxStunden; $i++) $stundenHeader .= "<th style=\"width: $percell%\">" . $i . "</th>";
			
			$absenzenHTML = "";
			for($i = 0; $i < sizeof($absenzen); $i++) {
				$absenzenHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate());
				
				if($absenzen[$i]->getDateAsSQLDate() != $absenzen[$i]->getEnddatumAsSQLDate()) $absenzenHTML .= "<br />bis "  . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate());
				
				$absenzenHTML .= "</td>";
				
				// $absenzenHTML .= "</td><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
				$absenzenHTML .= "<td>";
			
				if($absenzen[$i]->getKommentar() != "") {
					$absenzenHTML .= "<br/>" . $absenzen[$i]->getKommentar() . "";
				}
				if($absenzen[$i]->isBeurlaubung()) $absenzenHTML .= "<br />-- Beurlaubung --";
				else if($absenzen[$i]->isBefreiung()) $absenzenHTML .= "<br />-- Befreiung --";
			
			
				if(!$absenzen[$i]->isSchriftlichEntschuldigt()) $absenzenHTML .= "<br />-- Nicht schriftlich entschuldigt";
				else $absenzenHTML .= "<br />-- Schriftlich Entschuldigt";
				
				if(!$absenzen[$i]->isSchriftlichEntschuldigbar()) $absenzenHTML .= "<br /><b>!!! Kann nicht mehr schriftlich entschuldigt werden.</b>(Frist: " . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getSchriftlichEntschuldigbarDate()) . ")";
				
				$absenzenHTML.= "</td>";
				
				$stunden = $absenzen[$i]->getStundenAsArray();
				for($s = 1; $s <= $maxStunden; $s++) {
					$absenzenHTML .= "<td style=\"width: $percell%\" align=\"center\">";
					if(in_array($s,$stunden)) $absenzenHTML .= "X";
					else $absenzenHTML .= "&nbsp;";
					$absenzenHTML .= "</td>";
				}
				// $absenzenHTML .= "<td><a href=\"index.php?page=absenzensekretariat&currentDate=" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . "&noReturnMainView=1&openAbsenz={$absenzen[$i]->getID()}\">Bearbeiten / Löschen</a></td>";
			
				$absenzenHTML .= "</tr>";
			
			}
			
			$krankenzimmerHTML = "";
			
			for($i = 0; $i < sizeof($sanizimmerData); $i++) {
				$krankenzimmerHTML .= "<tr><td>" . date("d.m.Y",$sanizimmerData[$i]['sanizimmerTimeStart']) . "</td>";
				$krankenzimmerHTML .= "<td>" . date("H:i",$sanizimmerData[$i]['sanizimmerTimeStart']) . "</td>";
				$krankenzimmerHTML .= "<td>" . date("H:i",$sanizimmerData[$i]['sanizimmerTimeEnde']) . "</td>";
				$krankenzimmerHTML .= "<td>";
			
				switch($sanizimmerData[$i]['sanizimmerResult']) {
					case 'ZURUECK':
						$krankenzimmerHTML .= "Zurück in den Unterricht"; break;
			
					case 'BEFREIUNG':
						$krankenzimmerHTML .= "Befreiung ausgestellt"; break;
			
					case 'RETTUNGSDIENST':
						$krankenzimmerHTML .= "Abholung Rettungsdienst"; break;
			
				}
				$krankenzimmerHTML .= "</td>";
				$krankenzimmerHTML .= "<td>{$sanizimmerData[$i]['sanizimmerGrund']}</td></tr>";
				
			
			}
			
			$verspaetungHTML = "";
			
			for($i = 0; $i < sizeof($verspaetungData); $i++) {
				$verspaetungHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($verspaetungData[$i]['verspaetungDate']) . "</td>";
				$verspaetungHTML .= "<td>" . $verspaetungData[$i]['verspaetungMinuten'] . " Minuten<br />zur " . $verspaetungData[$i]['verspaetungStunde'] . ". Stunde</td>";
				$verspaetungHTML .= "<td>" . $verspaetungData[$i]['verspaetungKommentar'] . "</td></tr>";
			}
			
			eval("\$berichtHTML = \"" . DB::getTPL()->get("absenzen/berichte/print/schueler") . "\";");
			
			
			$berichtHTML = ($berichtHTML);
						
			$bericht = new PrintNormalPageA4WithHeader("Schülerbericht_" . $schueler->getCompleteSchuelerName());
			$bericht->setPrintedDateInFooter();
			$bericht->setHTMLContent($berichtHTML);
			
			$bericht->send();
			exit(0);
		}
		else {
			new errorPage("Zugriffsverletzung");
		}
	}
	
	private function notenmanagerExport() {
		if($this->isSekretariat) {
			$klassen = klasse::getAllKlassen();
			
			$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
			$xml .= "<nm_fehltage_interface version=\"1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">\r\n";
			$xml .= "<schulnummer>" . DB::getGlobalSettings()->schulnummer . "</schulnummer>\r\n";
			
			// Fix für den Notenmanager, da dieser das Schuljahr leider falsch prüft
			$schuljahr = DB::getSettings()->getValue("general-schuljahr");
			$schuljahr = explode("/",$schuljahr);
			$schuljahr[1] = "20" . $schuljahr[1];
			$schuljahr = implode("/",$schuljahr);
			
			$xml .= "<schuljahr>" . $schuljahr . "</schuljahr>\r\n";
  			$xml .= "<stand>" . date("d.m.Y") . "</stand>\r\n";
  			$xml .= "<schueler>\r\n";

			for($i = 0; $i < sizeof($klassen); $i++) {
				$schueler = $klassen[$i]->getSchueler();
				for($s = 0; $s < sizeof($schueler); $s++) {
						
					$absenzen = Absenz::getAbsenzenForSchueler($schueler[$s]);
						
					$absenzenCalculator = new AbsenzenCalculator($absenzen);
					$stat = $absenzenCalculator->getNotenmanagerStat();
		
					
					$xml .= "<schueler_fehltage>\r\n";
      				$xml .= "<asv_id>" . $schueler[$s]->getAsvID() . "</asv_id>\r\n";
					$xml .= "<name>" . $schueler[$s]->getName() . "</name>\r\n";
					$xml .= "<rufname>" . $schueler[$s]->getRufname() . "</rufname>\r\n";
					$xml .= "<geburtsdatum>" . $schueler[$s]->getGeburtstagAsNaturalDate() . "</geburtsdatum>\r\n";
					$xml .= "<fehltage>\r\n";
					$xml .= "<januar>" . $stat[1] . "</januar>\r\n";
					$xml .= "<februar>" . $stat[2] . "</februar>\r\n";
					$xml .= "<maerz>" . $stat[3] . "</maerz>\r\n";
					$xml .= "<april>" . $stat[4] . "</april>\r\n";
					$xml .= "<mai>" . $stat[5] . "</mai>\r\n";
					$xml .= "<juni>" . $stat[6] . "</juni>\r\n";
					$xml .= "<juli>" . $stat[7] . "</juli>\r\n";
					$xml .= "<august>" . $stat[8] . "</august>\r\n";
					$xml .= "<september>" . $stat[9] . "</september>\r\n";
					$xml .= "<oktober>" . $stat[10] . "</oktober>\r\n";
					$xml .= "<november>" . $stat[11] . "</november>\r\n";
					$xml .= "<dezember>" . $stat[12] . "</dezember>\r\n";
					$xml .= "</fehltage>\r\n";
					$xml .= "</schueler_fehltage>\r\n";
					
				}
			}
			
			
			$xml .= "</schueler>\r\n";
			$xml .= "</nm_fehltage_interface>";
			
			$xml = ($xml);
			
			header("Content-type: text/xml");
			
			header("Content-disposition: attachment; filename=\"" . basename("Notenmanager Fehltage.xml") . "\"");
				
			echo($xml);
			
			exit(0);
	
		}
		else {
			new errorPage("Diese Bericht ist nur für die Hauptbenutzer des Absenzenmoduls verfügbar!");
			exit(0);
		}
	}
	
	private function gradeStat() {
		if($this->isSekretariat) {
			$klassen = klasse::getAllKlassen();
			
			$bericht = new PrintNormalPageA4WithHeader("Klassenberichte");
			$bericht->setPrintedDateInFooter();
			
			
			for($i = 0; $i < sizeof($klassen); $i++) {
				$table = "";
				$schueler = $klassen[$i]->getSchueler();
				for($s = 0; $s < sizeof($schueler); $s++) {
					$table .= "<tr><td>" . ($s+1) . "</td><td>" . $schueler[$s]->getCompleteSchuelerName() . "</td>";
					
					$absenzen = Absenz::getAbsenzenForSchueler($schueler[$s]);
					
					$absenzenCalculator = new AbsenzenCalculator($absenzen);
					$absenzenCalculator->calculate();
					
					$stat = $absenzenCalculator->getNotenmanagerStat();
					
					$urlaub = $absenzenCalculator->getBeurlaubt();
					
					$gesamt = 0;
					for($m = 1; $m <= 12; $m++) {
						$table .= "<td>" . $stat[$m] . "</td>";
						$gesamt += $stat[$m];
					}
										
					$table .= "<td>" . $gesamt . "</td>";
					$table .= "<td>" . $urlaub . "</td>";
					
					$table .= "</tr>\r\n";
				}
				
				eval("\$klassenHTML = \"" . DB::getTPL()->get("absenzen/berichte/print/gradestat_grade") . "\";");
				$bericht->setHTMLContent($klassenHTML);
				
			}
			
			// eval("\$berichtHTML = \"" . DB::getTPL()->get("absenzen/berichte/print/gradestat") . "\";");
		
			
			$bericht->send();
			
			exit(0);
		}
		else {
			new errorPage("Dieser Bericht ist nur für die Hauptbenutzer des Absenzenmoduls verfügbar!");
			exit(0);
		}
	}
	
	private function fehlendeSchriftlicheEntschuldigungen() {
		$klasse = klasse::getByName($_REQUEST['grade']);
		
		$druck = new PrintNormalPageA4WithoutHeader("Fehlende Entschuldigungen Klasse " . $klasse->getKlassenName());
		
		
		if($klasse  == null) {
			new errorPage("Die angegebene Klasse ist leider nicht gültig!");
			exit(0);
		}
		
		if(!DB::getSession()->isAdmin() && !$klasse->isKlassenLeitung(DB::getSession()->getTeacherObject())) {
			new errorPage("Die angegebene Klasse ist leider nicht gültig! (Keine Klassenleitung!)");
			exit(0);
		}
		
		$schuelerHTML = "";
		
		$absenzenDerKlasse = Absenz::getAbsenzenForKlasse($klasse->getKlassenName());
		
		$schueler = $klasse->getSchueler();
		
		// $pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,L', 20, 30, 50, 50, $style, 'N');
		
		$style = array(
		    'border' => 2,
		    'vpadding' => 'auto',
		    'hpadding' => 'auto',
		    'fgcolor' => array(0,0,0),
		    'bgcolor' => false, //array(255,255,255)
		    'module_width' => 1, // width of a single module in points
		    'module_height' => 1 // height of a single module in points
		);
		
		
		$qrCode = $druck->serializeTCPDFtagParameters('www.tcpdf.org', 'QRCODE,L', 20, 30, 50, 50, $style, 'N');
		
		
		for($i = 0; $i < sizeof($schueler); $i++) {
			
			
			$hasAbsenzen = false;
			$absenzen = array();
			for($a = 0; $a < sizeof($absenzenDerKlasse); $a++) {
				if($absenzenDerKlasse[$a]->getSchueler()->getAsvID() == $schueler[$i]->getAsvID()) {
					if(!$absenzenDerKlasse[$a]->isSchriftlichEntschuldigt()) {
						$hasAbsenzen = true;
						$absenzen[] = $absenzenDerKlasse[$a];
					}
					
				}
			}
			
			if($hasAbsenzen) {
				
				$absenzenHTML = "";
				
				for($a = 0; $a < sizeof($absenzen); $a++) {
					$absenzenHTML .= "<tr><td>"  . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$a]->getDateAsSQLDate());
					if($absenzen[$a]->getDateAsSQLDate() != $absenzen[$a]->getEnddatumAsSQLDate()) {
						$absenzenHTML .= " bis " . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$a]->getEnddatumAsSQLDate());
					}
					
					$absenzenHTML .= "</td><td>";
					$absenzenHTML .= $absenzen[$a]->getStundenAsString();
					$absenzenHTML .= "</td><td>";
					
					if($absenzen[$a]->isBefreiung()) $absenzenHTML .= "Vorzeitige Befreiung vom Unterricht";
					if($absenzen[$a]->isBeurlaubung()) $absenzenHTML .= "Beurlaubung vom Unterricht";
					
					$absenzenHTML .= "</td></tr>";
					
				}
				
				eval("\$schuelerHTML .= \"" . DB::getTPL()->get("absenzen/berichte/print/fehlende_entschuldigungen_schueler") . "\";");
			}
		}
		
		eval("\$berichtHTML = \"" . DB::getTPL()->get("absenzen/berichte/print/fehlende_entschuldigungen") . "\";");
		
			
		$berichtHTML = ($berichtHTML);
			
		
		$druck->setHTMLContent($berichtHTML);
		$druck->send();
		exit(0);
	}
	
	private function tagesBericht() {
		
		$tag = explode(", ",$_POST['dayDate']);
		
		if(sizeof($tag) != 2) {
			new errorPage("Die Datumsangabe ist leider nicht korrekt!");
		}
		
		$tag = $tag[1];
		
		if(!DateFunctions::isNaturalDate($tag)) {
			new errorPage("Die Datumsangabe ist leider nicht korrekt!");
		}
		
		$absenzen = Absenz::getAbsenzenForDate(DateFunctions::getMySQLDateFromNaturalDate($tag), "");
		
		$maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
		
		$percell = 40 / $maxStunden;
		
		
		for($i = 1; $i <= $maxStunden; $i++) $tabellenStunden .= "<th style=\"width:$percell%\">" . $i . "</th>";
		
			for($i = 0; $i < sizeof($absenzen); $i++) {
				
			$krankmeldungenHTML .= "<tr><td>" . $absenzen[$i]->getSchueler()->getKlasse() . "</td>";
			$krankmeldungenHTML .= "<td>" . $absenzen[$i]->getSchueler()->getCompleteSchuelerName();

				
			if($absenzen[$i]->isBefreiung()) {
				$krankmeldungenHTML .= " | <b>Befreiung</b>";
			}
				
				
			if($absenzen[$i]->isBeurlaubung()) {
				$krankmeldungenHTML .= " | <b>Beurlaubung</b>";
				if($absenzen[$i]->getBeurlaubung()->isInternAbwesend()) {
					$krankmeldungenHTML .= " (<i>Intern abwesend</i>)";
				}
					
			}
				
			$krankmeldungenHTML .= "</td>";
				
			$stunden = $absenzen[$i]->getStundenAsArray();
				
			if($absenzen[$i]->getDateAsSQLDate() != $absenzen[$i]->getEnddatumAsSQLDate()) {
				$stunden = array();
				for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
					$stunden[] = $s;
				}
			}
				
			for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
				$krankmeldungenHTML .= "<td>" . ((in_array($s,$stunden)) ? "X" : "&nbsp;") . "</td>";
			}
				
			if($absenzen[$i]->isMehrtaegig()) {
				$krankmeldungenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
			}
			else {
				$krankmeldungenHTML .= "<td>&nbsp;</td>";
			}
				
			if($absenzen[$i]->isBefreiung() && !$absenzen[$i]->getBefreiung()->isPrinted()) {
				$hasToPrint[] = $absenzen[$i];
			}
				
				
			$krankmeldungenHTML .= "</tr>";
			
			if($absenzen[$i]->getKommentar() != "" && $_POST['tagesbericht'] == 2) {
				$krankmeldungenHTML .= "<tr><td>&nbsp;</td>";
				$krankmeldungenHTML .= "<td colspan=\"" . ($maxStunden +2) . "\">";
				
				switch($absenzen[$i]->getKanal()) {
					case 'TELEFON':
						$krankmeldungenHTML .= "Per Telefon | ";
					break;
					
					case 'WEBPORTAL':
						$krankmeldungenHTML .= "Per Portal | ";
						break;
						
					case 'LEHRER':
						$krankmeldungenHTML .= "Meldung durch Lehrer | ";
						break;
						
					case 'PERSOENLICH':
						$krankmeldungenHTML .= "Persönlich / Bote | ";
						break;
						
					case 'FAX':
						$krankmeldungenHTML .= "Per Fax | ";
						break;
						
				}
				
				$krankmeldungenHTML .= nl2br($absenzen[$i]->getKommentar()) . "</td></tr>";
			}
		}

	
		$data = explode(".",$tag);
		$timeStart = mktime(0,0,0,$data[1],$data[0],$data[2]);
		$timeEnde = mktime(23,59,59,$data[1],$data[0],$data[2]);
		
		$sanizimmer = DB::getDB()->query("SELECT * FROM absenzen_sanizimmer JOIN schueler ON sanizimmerSchuelerAsvID=schuelerAsvID WHERE sanizimmerTimeStart > $timeStart AND sanizimmerTimeStart < $timeEnde ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		$sanizimmerHTML = "";
		while($s = DB::getDB()->fetch_array($sanizimmer)) {
			$sanizimmerHTML .= "<tr>";
			$sanizimmerHTML .= "<td>" . $s['schuelerKlasse'] . "</td>";
			$sanizimmerHTML .= "<td>" . $s['schuelerName'] . ", " . $s['schuelerRufname'] . "</td>";
			$sanizimmerHTML .= "<td>" . date("H:i",$s['sanizimmerTimeStart']) . "</td>";
			$sanizimmerHTML .= "<td>" . date("H:i",$s['sanizimmerTimeEnde']) . "</td>";
			$sanizimmerHTML .= "<td>";
			switch($s['sanizimmerResult']) {
				case 'ZURUECK': $sanizimmerHTML .= "Zurück in den Unterricht"; break;
				case 'BEFREIUNG': $sanizimmerHTML .= "Befreiung vom Unterricht"; break;
				case 'RETTUNGSDIENST': $sanizimmerHTML .= "Abholung durch Rettungsdienst"; break;
			}
			$sanizimmerHTML .= "</td></tr>";
		}
		
		// Verspätungen
		
		$verspaetungen = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen JOIN schueler on verspaetungSchuelerAsvID=schuelerAsvID WHERE verspaetungDate='" . DateFunctions::getMySQLDateFromNaturalDate($tag) . "' ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		$verspaetungHTML = "";
		while($s = DB::getDB()->fetch_array($verspaetungen)) {
			$verspaetungHTML .= "<tr>";
			$verspaetungHTML .= "<td>" . $s['schuelerKlasse'] . "</td>";
			$verspaetungHTML .= "<td>" . $s['schuelerName'] . ", " . $s['schuelerRufname'] . "</td>";
			$verspaetungHTML .= "<td>" . $s['verspaetungMinuten'] . "<br />Zur " . $s['verspaetungStunde'] . ". Stunde</td>";
			$verspaetungHTML .= "<td>" . $s['verspaetungKommentar'] . "</td>";
			$verspaetungHTML .= "</tr>";
		}
		

		eval("\$berichtHTML = \"" . DB::getTPL()->get("absenzen/berichte/print/day_detailed") . "\";");
			
		$berichtHTML = ($berichtHTML);
		
		$bericht = new PrintNormalPageA4WithHeader("Absenzen Tagesbericht" . (($_POST['tagesbericht'] == 2) ? (" mit Details") : ("")) . " - " . $tag);
		$bericht->setPrintedDateInFooter();
		$bericht->setHTMLContent($berichtHTML);
		$bericht->send();
		exit(0);
		
	}
	
	public function showIndex() {
		if($this->isSekretariat) {
			$currentDate = DateFunctions::getTodayAsNaturalDate();
			
			$dayNames = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
			$dayName = $dayNames[date("N")-1];
			
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/berichte/index") . "\");");
			PAGE::kill(true);
			//exit(0);
		}
		
		else {
			new errorPage("Diese Seite ist leider nicht verfügbar!");
		}
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
		return array(
				array(
					'name' => "absenzen-attestnachdreitagen",
					'typ' => BOOLEAN,
					'titel' => "Attest nach 3 Tagen fordern?",
					'text' => "Soll ein Attest nach drei Tagen Abwesenheit gefordert werden?"
				)
		);
	}
	
	
	public static function getSiteDisplayName() {
		return 'Absenzenmodul (Berichte)';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Absenzen_Admin';
	}
	
	public static function dependsPage() {
		return ['absenzenstatistik','absenzensekretariat'];
	}
}


?>