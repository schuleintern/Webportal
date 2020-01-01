<?php


/**
 * Diese Klasse dient der Eingabe der Beobachtungen zu allen Schülern.
 * @author Christian
 */
class beobachtungsbogenklassenleitung extends AbstractPage {
	
	private $bogen = NULL;
	private $bogenID = NULL;
	private $klasse = NULL;
	private $myUserID = NULL;
	private $myRealName = NULL;
	private $schueler = array();
	private $fragen = array();
	private $faecher = array();
	
	public function __construct() {
		
		$this->needLicense = true;
		
		parent::__construct(array("Beobachtungsbogen", "Beobachtungsbogen - Klassenleiter"));
		
		$this->checkLogin();
		
		if(!DB::getSession()->isTeacher()) {
			new error("Dieser Bereich ist nur für Lehrer zur Verfügung!");
			exit(0);
		}
		
	}
	
	public function execute() {
		$mode = $_GET['mode'];
		$bogenID = intval($_GET['bogenID']);
		
		$currentBoegen = $this->getCurrentBoegen();
		
		if(sizeof($currentBoegen) == 0) {
			eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/keinbogen") . "\");");
			exit(0);
		}
		
		$bogenOK = false;
		if($bogenID > 0) {
			for($i = 0; $i < sizeof($currentBoegen); $i++) {
				if($currentBoegen[$i]['beobachtungsbogenID'] == $bogenID) {
					$this->bogen = $currentBoegen[$i];
					$this->bogenID = $this->bogen['beobachtungsbogenID'];
					$bogenOK = true;
				}
			}
		}
		else {
			if(sizeof($currentBoegen) == 1) {
				// nur ein Bogen --> Umleiten
				header("Location: index.php?page=beobachtungsbogenklassenleitung&bogenID=" . $currentBoegen[0]['beobachtungsbogenID']);
				exit(0);
			}
			else {
				// Auswahl der Bögen anzeigen
				for($i = 0; $i < sizeof($currentBoegen); $i++) {
					$bogen = $currentBoegen[$i];
					
					eval("\$bogenHTML .= \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/multibogen_bit") . "\";");
				}
				
				if($bogenHTML == "") $bogenHTML = "<tr><td colspan=\"5\" style=\"text-align: center\"><strong><i class=\"fa fa-ban\"></i> Keiner vorhanden</strong></td></tr>";
				
				eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/multibogen") . "\");");
				exit(0);
			}
		}
		
		if(!$bogenOK) {
			new error("Der angeforderte Beobachtungsbogen ist ungültig!");
			exit(0);
		}
		
		
		
		switch($mode) {
			default:
				$this->showIndex();
			break;
			
			case "viewBeobachtungen":
				$this->viewBeobachtungen();
			break;
			
			case "viewBeobachtungenPrint":
				$this->viewBeobachtungenPrint();
			break;
			
			case "viewResults":
				$this->viewResults();
			break;
			
			case "viewResultsPrint":
				$this->viewResultsPrint();
			break;
			
			case "printBoegen":
				$this->printBoegen();
			break;
			
			case "getOldFile":
				$this->getOldFile();
			break;
		}	
	}
	
	private function getOldFile() {
		$this->checkGradeAccess();
		
		$file = $_GET['file'];
		
		if($file != "") {
			$data = explode("-",$file);
			if($data[0] == $this->bogenID && $data[1] == $this->klasse) {
				if(file_exists("../data/beobachtungsboegen/" . $file . ".pdf")) {
					header('Content-Description: Dateidownload');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="'."Beobachtungsboegen_" . $data[1] . "_" .$data[3] . ".pdf".'"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: ' . filesize("beobachtungsboegen/" . $file . ".pdf"));
					readfile("../data/beobachtungsboegen/" . $file . ".pdf");
					
					exit(0);
				}
				else {
					new error("Der angegebene Bogen kann nicht gefunden werden!<br /><pre>" . "beobachtungsboegen/" . $file . ".pdf" . "</pre>");
					exit(0);
				}
			}
			else {
				new error("Der angegebene Bogen passt nicht!<br /><pre>" . "beobachtungsboegen/" . $file . ".pdf" . "</pre>");
				exit(0);
			}
		}
	}
	
	private function printBoegen() {
		$this->checkGradeAccess();
		
		if(!DB::getSession()->isSavedSession()) {
			eval("\$warningSession =\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/warningSession") . "\";");
		}
		else $warningSession = "";
		
		if($_GET['doPrint'] > 0) {
			// $print = ($print);
			
			$mpdf=new mPDF('utf-8', 'A4-P');
			
			$mpdf->ignore_invalid_utf8 = true;
			
			$this->bogen['beobachtungsbogenDatum'] = functions::getFormatedDateFromSQLDate($this->bogen['beobachtungsbogenDatum']);
			
			
			eval("\$header = \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/print_header") . "\";");
			
			$header = ($header);
			
			$mpdf->WriteHTML($header,1);
			
			$klasse = $_POST['klasse'];
						
			
			for($i = 0; $i < sizeof($this->schueler); $i++) {
				$results = $this->getFragenResultsForPupil($this->schueler[$i]['userID']);
			
				$name = $_POST['name_' . $this->schueler[$i]['userID']];
				
				$fragenHTML = "";
				
				$mpdf->Bookmark($name);
			
				$lineA = true;
				
				for($f = 0; $f < sizeof($this->fragen); $f++) {
					$option1 = " - ";
					$option2 = " - ";
					$option3 = " - ";
					$option4 = " - ";
					$option5 = " - ";
					
					if($lineA) $bgColor = "#CDCDCD";
					else $bgColor = "#FFFFFF";
					
					// $frageResult = $results[$this->fragen[$f]['frageID']];
					
					$frageResult = $_POST[$this->schueler[$i]['userID'] . "-" . $this->fragen[$f]['frageID']];
					
					if($frageResult != "k") {
						if($frageResult <= -1.5) {
							$option1 = "X";
						}
						elseif($frageResult <= -0.5) {
							$option2 = "X";
						}
						elseif($frageResult <= 0.5) {
							$option3 = "X";
						}
						elseif($frageResult <= 1.5) {
							$option4 = "X";
						}
						else {
							$option5 = "X";
						}
					}
					
					eval("\$fragenHTML .= \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/print_frage_typ{$this->fragen[$f]['frageTyp']}") . "\";");
					
					$lineA = !$lineA;
				}
				
				if(strpos($this->bogen['beobachtungsbogenText'], "{TABELLE}") > 0) {
				
					$einleitung = substr($this->bogen['beobachtungsbogenText'],0,strpos($this->bogen['beobachtungsbogenText'], "{TABELLE}"));
					$footer = substr($this->bogen['beobachtungsbogenText'],strpos($this->bogen['beobachtungsbogenText'], "{TABELLE}")+9);
					
					eval("\$tabelle = \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/tabelle") . "\";");
					
					$content = "<span class=\"einleitung\">" . $einleitung . "</span>" . $tabelle . "<span class=\"einleitung\">" . $footer . "</span>";
				}
				else {
					$content = "<h3>Falsche Konfiguration! Es ist keine Position für die Bewertungstabelle angegeben. {TABELLE} muss im Text auf dem Bewertungsbogen an der Stelle der Tabelle verwendet werden!</h3>";
				}
				
				eval("\$SCHUELER = \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/print_schueler") . "\";");
					
				$SCHUELER = ($SCHUELER);
				
				$mpdf->WriteHTML($SCHUELER,2);
				if($i != (sizeof($this->schueler)-1)) $mpdf->AddPage();
				
				// if($i == 1) break;
			}
			
			$mpdf->Output("../data/beobachtungsboegen/" . $this->bogenID . "-" . $this->klasse . "-" . time() . "-" . $this->myRealName . ".pdf","F");
			
			$mpdf->Output("Beobachtungsboegen_" . $this->klasse . ".pdf",'D');
			
			
			
			
			exit(0);
		}
		
		// Bisher gedruckte Bögen
		
		$oldFiles = "";
		$dir = opendir("../data/beobachtungsboegen");
		while($file = readdir($dir)) {
			list($bID,$klasse,$zeit,$lehrer) = explode("-",str_replace(".pdf", "", $file));
			if($bID == $this->bogenID && $this->klasse == $klasse) {
				$oldFiles .= "<tr><td>" . $lehrer . "</td><td>" . functions::makeDateFromTimestamp($zeit) . "</td>";
				$oldFiles .= "<td><a href=\"index.php?page=beobachtungsbogenklassenleitung&bogenID=1&grade=" . $this->klasse . "&mode=getOldFile&file=" . str_replace(".pdf", "", $file) . "\"><i class=\"fa fa-file-pdf-o\"></i> PDF herunterladen</a></td></tr>";
			}
		}
		
		if($oldFiles == "") $oldFiles = "<tr><td colspan=\"3\"><center><b>Bisher wurden keine Beobachtungsbögen erzeugt</b></center></td></tr>";
		
		$MATRIX = "<tr><th width=\"20%\">Schüler / Frage</th>";
		
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			if($this->fragen[$i]['frageTyp'] == 2) {
				$only = "<br /><small>nur <i class=\"fa fa-smile-o\"></i><i class=\"fa fa-smile-o\"></i> bis <i class=\"fa fa-meh-o\"></i></small>";
			}
			else $only = "";
			$MATRIX .= "<th>" . $this->fragen[$i]['frageText'] . "$only</th>";
		}
		
		$MATRIX .= "</tr>";
		
		for($i = 0; $i < sizeof($this->schueler); $i++) {
			$results = $this->getFragenResultsForPupil($this->schueler[$i]['userID']);
				
			$MATRIX .= "<tr><td><input type=\"text\" name=\"name_" . $this->schueler[$i]['userID'] . "\" class=\"form form-control\" value=\"" . $this->schueler[$i]['userFirstName'] . " " . $this->schueler[$i]['userLastName'] . "\"></td>";
				
			for($f = 0; $f < sizeof($this->fragen); $f++) {
				$MATRIX .= "<td>";
				
				$frageResult = $results[$this->fragen[$f]['frageID']];
				//if($frageResult['anzahl'] == 0) $MATRIX .= "<p class=\"text-red\">Keine Wertung, da keine abgegebene Stimme</p>";
				//else {
				//	$MATRIX .= $this->getSmileysFAFromAVG($frageResult['note']);
				//}
				$MATRIX .= $this->getSelectFromAVG($frageResult['note'],$frageResult['anzahl'],$this->schueler[$i]['userID'],$this->fragen[$f]['frageID'],$this->fragen[$f]['frageTyp']);
				
				$MATRIX .= "</td>";
			}
				
			$MATRIX .= "</tr>";
		}
		
		
		$this->bogen['beobachtungsbogenDatum'] = functions::getFormatedDateFromSQLDate($this->bogen['beobachtungsbogenDatum']);
		
		eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/print/index") . "\");");
		
	}
	
	private function getSelectFromAVG($note,$anzahl,$userID,$frage,$frageTyp) {
		$html = "<select name=\"" . $userID . "-" . $frage . "\" class=\"form-control\" style=\"font-family:'FontAwesome', Arial;\">";
		
		$selected = array(
				"","","","","",""
		);
		
		if($anzahl > 0) {
			if($note <= -1.5)
				$selected[1] = " selected=\"selected\"";
			elseif($note <= -0.5)
				$selected[2] = " selected=\"selected\"";
			elseif($note < 0.5)
				$selected[3] = " selected=\"selected\"";
			elseif($note < 1.5)
				$selected[4] = " selected=\"selected\"";
			elseif($note >= 1.5)
				$selected[5] = " selected=\"selected\"";
			
				
		}
		else $selected[0] = " selected=\"selected\"";
		
		
		$html .= "<option value=\"k\"{$selected[0]}>Keine Wertung</option>";
		$html .= "<option value=\"-2\"{$selected[1]}>&#xf118;&#xf118;</option>";
		$html .= "<option value=\"-1\"{$selected[2]}>&#xf118;</option>";
		$html .= "<option  value=\"0\"{$selected[3]}>&#xf11a;</option>";
		if($frageTyp == 1) $html .= "<option  value=\"1\"{$selected[4]}>&#xf119;</option>";
		if($frageTyp == 1) $html .= "<option  value=\"2\"{$selected[5]}>&#xf119;&#xf119;</option>";
		
		$html .= "</select>\r\n";
		
		return $html;
	}
	
	/*private function getSmileysFAFromAVG($vote) {
		if($vote <= -1.5)
			return "<i class=\"fa fa-smile-o\"></i><i class=\"fa fa-smile-o\"></i>";
		elseif($vote <= -0.5)
		return "<i class=\"fa fa-smile-o\"></i>";
		elseif($vote < 0.5)
		return "<i class=\"fa fa-meh-o\"></i>";
		elseif($vote < 1.5)
		return "<i class=\"fa fa-frown-o\"></i>";
		elseif($vote >= 1.5)
		return "<i class=\"fa fa-frown-o\"></i><i class=\"fa fa-frown-o\"></i>";
		else
			return "<i class=\"fa fa-ban\"></i>";
	}*/
	
	private function viewResults() {
		$this->checkGradeAccess();
		
		$MATRIX = "<tr><th>Schüler / Frage</th>";
		
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			if($this->fragen[$i]['frageTyp'] == 2) {
				$only = "<br /><small>nur <i class=\"fa fa-smile-o\"></i><i class=\"fa fa-smile-o\"></i> bis <i class=\"fa fa-meh-o\"></i></small>";
			}
			else $only = "";
			$MATRIX .= "<th>" . $this->fragen[$i]['frageText'] . "$only</th>";
		}
		
		$MATRIX .= "</tr>";
		
		for($i = 0; $i < sizeof($this->schueler); $i++) {
			$results = $this->getFragenResultsForPupil($this->schueler[$i]['userID']);
			
			$MATRIX .= "<tr><td>" . $this->schueler[$i]['userLastName'] . ", " . $this->schueler[$i]['userFirstName'] . "</td>";
			
			for($f = 0; $f < sizeof($this->fragen); $f++) {
				$MATRIX .= "<td>";
				
				$frageResult = $results[$this->fragen[$f]['frageID']];
				if($frageResult['anzahl'] == 0) $MATRIX .= "<i>Keine Wertung, da keine abgegebene Stimme</i>";
				else {
					$MATRIX .= $this->getSmileysFAFromAVG($frageResult['note']) . "<br /><small>aus " . $frageResult['anzahl'] . " Stimmen</small>";
				}
				$MATRIX .= "</td>";
			}
			
			$MATRIX .= "</tr>";
		}

		
		eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/viewresults/index") . "\");");
		exit(0);
	}
	
	private function viewResultsPrint() {
		$this->checkGradeAccess();
		
		$smiley1 = "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
		$smiley2 = "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
		$smiley3 = "<img src=\"images/beobachtungsbogen/2.jpg\" width=\"10\">";
		$smiley4 = "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
		$smiley5 = "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
		
	
		$MATRIX = "<tr><th>Schüler / Frage</th>";
	
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			if($this->fragen[$i]['frageTyp'] == 2) {
				$only = "<br /><small>nur $smiley1 bis $smiley3</small>";
			}
			else $only = "";
			$MATRIX .= "<th>" . $this->fragen[$i]['frageText'] . "$only</th>";
		}
	
		$MATRIX .= "</tr>";
	
		for($i = 0; $i < sizeof($this->schueler); $i++) {
			$results = $this->getFragenResultsForPupil($this->schueler[$i]['userID']);
				
			$MATRIX .= "<tr><td>" . $this->schueler[$i]['userLastName'] . ", " . $this->schueler[$i]['userFirstName'] . "</td>";
				
			for($f = 0; $f < sizeof($this->fragen); $f++) {
				$MATRIX .= "<td>";
	
				$frageResult = $results[$this->fragen[$f]['frageID']];
				if($frageResult['anzahl'] == 0) $MATRIX .= "<i>Keine Wertung, da keine abgegebene Stimme</i>";
				else {
					$MATRIX .= $this->getSmileysFAFromAVGPrint($frageResult['note']) . "<br /><small>aus " . $frageResult['anzahl'] . " Stimmen</small>";
				}
				$MATRIX .= "</td>";
			}
				
			$MATRIX .= "</tr>";
		}
		
		$heute = functions::makeDateFromTimestamp(time());
		
		
		eval("\$printHTML = \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/viewresults/print") . "\";");
			
		$printHTML = ($printHTML);
			
		$mpdf=new mPDF('utf-8', 'A4-L');
		$mpdf->Bookmark("Ergebisse " . $this->klasse);
		$mpdf->WriteHTML($printHTML);
		$mpdf->Output("Ergebisse_Klasse_" . $this->klasse . ".pdf","D");
		
		exit(0);
	}
	
	private function viewBeobachtungen() {
		$this->checkGradeAccess();
		
		
		$fragenHTML = "";
		
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			$fragenHTML .= "<tr><td>" . ($i+1) . "</td><td>" . $this->fragen[$i]['frageText'] . "</td></tr>\n";
		}
		
		$MATRIX = "<tr><th>Schüler/in / Fach</th>";
		
		for($i = 0; $i < sizeof($this->faecher); $i++) {
			$MATRIX .= "<th>" . $this->faecher[$i]['fachName'] . "<br /><smalL>" . $this->faecher[$i]['lehrerKuerzel'] . "</small></th>";
		}
		$MATRIX .= "</tr>";
		
		for($i = 0; $i < sizeof($this->schueler); $i++) {
			
			$data = $this->getFragenDataForPupil($this->schueler[$i]['userID']);
		
			
			$MATRIX .= "<tr><td>" . $this->schueler[$i]['userLastName'] . ", " . $this->schueler[$i]['userFirstName'] . "</td>";
			
			for($f = 0; $f < sizeof($this->faecher); $f++) {
				
				
				$MATRIX .= "<td>";
				
				for($g = 0; $g < sizeof($this->fragen); $g++) {
					$MATRIX .= "\t#" . ($g+1) . ": " . $this->getSmileysFA($data[$this->faecher[$f]['lehrerKuerzel']][$this->faecher[$f]['fachName']][$this->fragen[$g]['frageID']]) . "<br />\n";
				}
				
				$MATRIX .= "</td>\n";
			}
		}
		
		eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/viewbeobachtungen/index") . "\");");
		exit(0);
	}
	
	private function viewBeobachtungenPrint() {
		$this->checkGradeAccess();
	
	
		$fragenHTML = "";
	
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			$fragenHTML .= "<tr><td>" . ($i+1) . "</td><td>" . $this->fragen[$i]['frageText'] . "</td></tr>\n";
		}
	
		$MATRIX = "<tr><th>Schüler/in / Fach</th>";
	
		for($i = 0; $i < sizeof($this->faecher); $i++) {
			$MATRIX .= "<th>" . $this->faecher[$i]['fachName'] . "<br /><smalL>" . $this->faecher[$i]['lehrerKuerzel'] . "</small></th>";
		}
		$MATRIX .= "</tr>";
	
		for($i = 0; $i < sizeof($this->schueler); $i++) {
				
			$data = $this->getFragenDataForPupil($this->schueler[$i]['userID']);
	
				
			$MATRIX .= "<tr><td>" . $this->schueler[$i]['userLastName'] . ", " . $this->schueler[$i]['userFirstName'] . "</td>";
				
			for($f = 0; $f < sizeof($this->faecher); $f++) {
	
	
				$MATRIX .= "<td>";
	
				for($g = 0; $g < sizeof($this->fragen); $g++) {
					$MATRIX .= "\t#" . ($g+1) . ": " . $this->getSmileysPrint($data[$this->faecher[$f]['lehrerKuerzel']][$this->faecher[$f]['fachName']][$this->fragen[$g]['frageID']]) . "<br />\n";
				}
	
				$MATRIX .= "</td>\n";
			}
		}
		
		$heute = functions::makeDateFromTimestamp(time());
		
		
		eval("\$printHTML = \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/viewbeobachtungen/print") . "\";");
			
		$printHTML = ($printHTML);
			
		$mpdf=new mPDF('utf-8', 'A4-L');
		$mpdf->Bookmark("Beobachtungen " . $this->klasse);
		$mpdf->WriteHTML($printHTML);
		$mpdf->Output("Alle_Beobachtungen_Klasse_" . $this->klasse . ".pdf","D");
		
		exit(0);
	}
	

	
	private function getSmileysFA($vote) {
		switch($vote) {
			case -2:
				return "<i class=\"fa fa-smile-o\"></i><i class=\"fa fa-smile-o\"></i>";
			case -1:
				return "<i class=\"fa fa-smile-o\"></i>";
			case "0":
				return "<i class=\"fa fa-meh-o\"></i>";
			case 1:
				return "<i class=\"fa fa-frown-o\"></i>";
			case 2:
				return "<i class=\"fa fa-frown-o\"></i><i class=\"fa fa-frown-o\"></i>";
			default:
				return "<i class=\"fa fa-ban\"></i>";
		}
	}
	
	private function getSmileysFAFromAVG($vote) {
		if($vote <= -1.5)
				return "<i class=\"fa fa-smile-o\"></i><i class=\"fa fa-smile-o\"></i>";
		elseif($vote <= -0.5)
				return "<i class=\"fa fa-smile-o\"></i>";
		elseif($vote < 0.5)
				return "<i class=\"fa fa-meh-o\"></i>";
		elseif($vote < 1.5)
				return "<i class=\"fa fa-frown-o\"></i>";
		elseif($vote >= 1.5)
				return "<i class=\"fa fa-frown-o\"></i><i class=\"fa fa-frown-o\"></i>";
		else
				return "<i class=\"fa fa-ban\"></i>";
	}
	
	private function getSmileysFAFromAVGPrint($vote) {
		if($vote <= -1.5)
			return "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
		elseif($vote <= -0.5)
			return "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
		elseif($vote < 0.5)
			return "<img src=\"images/beobachtungsbogen/2.jpg\" width=\"10\">";
		elseif($vote < 1.5)
			return "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
		elseif($vote >= 1.5)
			return "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
		else
			return "-";
	}
	
	private function getSmileysPrint($vote) {
		switch($vote) {
			case -2:
				return "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
			case -1:
				return "<img src=\"images/beobachtungsbogen/1.jpg\" width=\"10\">";
			case "0":
				return "<img src=\"images/beobachtungsbogen/2.jpg\" width=\"10\">";
			case 1:
				return "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
			case 2:
				return "<img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\"><img src=\"images/beobachtungsbogen/3.jpg\" width=\"10\">";
			default:
				return "-";
		}
	}
	
	private function getCurrentBoegen() {
		$boegen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_boegen WHERE beobachtungsbogenDatum >= CURDATE() AND beobachtungsbogenStartDate <= CURDATE()");
		$result = array();
		while($b = DB::getDB()->fetch_array($boegen)) {
			$result[] = $b;
		}
		
		return $result;
	}
	
	/**
	 * Prüft, ob der Lehrer Zugriff auf diese Klasse hat.
	 */
	private function checkGradeAccess() {
		$myName = DB::getSession()->getTeacherObject()->getKuerzel();
		
		$lehrer = DB::getDB()->query_first("SELECT * FROM beobachtungsbogen_klassenleitung WHERE klassenleitungUserID = '" . DB::getUserID() . "' AND beobachtungsbogenID='" . $this->bogenID . "' AND klassenName='" . addslashes($_REQUEST['grade']) . "'");
		if($lehrer['beobachtungsbogenID'] > 0 || DB::getSession()->isSchulleitung() || DB::getSession()->isMember("Webportal_Leistungsbericht_Admin")) {
			$this->klasse = addslashes($_REQUEST['grade']);
			$this->myRealName = DB::getSession()->getTeacherObject()->getKuerzel();
			$this->myUserID = DB::getSession()->getUserID();
			
			$this->schueler = array();
			
			$klasse = klasse::getByName($this->klasse);
			if($klasse != null) {
				$schueler = $klasse->getSchueler(false);
				
				for($i = 0; $i < sizeof($schueler); $i++) {
					$this->schueler[] = array(
							"userID" => $schueler[$i]->getSchuelerUserID(),
							"userFirstName" => $schueler[$i]->getRufname(),
							"userLastName" => $schueler[$i]->getName()
					);
				}
			}
			
// 			$this->schueler = pupil::getUsersOfGrade($this->klasse);
			$this->fragen = $this->getFragenData();
			
			$faecher = DB::getDB()->query("SELECT * FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='" . $this->bogenID . "' AND klasseName='" . $this->klasse . "' ORDER BY fachName");
			while($f = DB::getDB()->fetch_array($faecher)) {
				$this->faecher[] = $f;
			}
		}
		else {
			new error("Zugriffsfehler: Kein Zugriff auf diese Klasse als Klasenleitung!");
			exit(0);
		}
	}
	
	// $bewertung = "X", wenn "keine Auswahl"
	private function saveForPupil($userID, $frageID, $bewertung) {
		// echo("save: " . $userID . ":" . $frageID . "-" . $bewertung . "<br />");
		if($bewertung == "x" || $bewertung == "X" || $bewertung == "" || $bewertung == " ") {
			DB::getDB()->query("DELETE FROM beobachtungsbogen_fragen_daten WHERE frageID='" . $frageID . "' AND schuelerID='" . $userID . "'");
		}
		else {
			DB::getDB()->query("INSERT INTO beobachtungsbogen_fragen_daten (frageID, schuelerID, bewertung) values('$frageID','$userID','$bewertung') ON DUPLICATE KEY UPDATE bewertung='$bewertung'");
		}
	}
	
	
	private function getFragenDataForPupil($userID) {
		$result = array();
		$data = DB::getDB()->query("SELECT * FROM beobachtungsbogen_fragen_daten WHERE frageID IN (SELECT frageID FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='" . $this->bogenID . "') AND schuelerID='" . $userID . "'");
		while($d = DB::getDB()->fetch_array($data)) {
			
			$result[$d['lehrerKuerzel']][$d['fachName']][$d['frageID']] = $d['bewertung'];
		}
		
		return $result;
	}
	
	private function getFragenResultsForPupil($userID) {
		$result = array();
		
		for($i = 0; $i < sizeof($this->fragen); $i++) {
			$result[$this->fragen[$i]['frageID']] = array(
				"anzahl" => 0,
				"note" => "x"
			);
			$avg = DB::getDB()->query_first("SELECT AVG(bewertung) AS NOTE, COUNT(bewertung) AS ANZAHL FROM beobachtungsbogen_fragen_daten WHERE schuelerID='" . $userID . "' AND frageID='" . $this->fragen[$i]['frageID'] . "'");
			if($avg['ANZAHL'] > 0) {
				$result[$this->fragen[$i]['frageID']]['anzahl'] = $avg['ANZAHL'];
				$result[$this->fragen[$i]['frageID']]['note'] = $avg['NOTE'];
			}
		}
		
		return $result;
		
	}
	
	private function getFragenData() {
		$result = array();
		$fragen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='" . $this->bogenID . "'");
		while($f = DB::getDB()->fetch_array($fragen)) {
			$result[] = $f;
		}
		
		return $result;
	}
	
	private function showIndex() {
		// Meine Klassen
		$myName = DB::getSession()->getTeacherObject()->getKuerzel();
		
		$deadline = functions::getFormatedDateFromSQLDate($this->bogen['beobachtungsbogenDeadline']);
		
		$isAdmin = false;
		$isSchulleitung = false;
		
		if(DB::getSession()->isSchulleitung()) {
			$isSchulleitung = true;
			$addSQL = "";
			$SQL = "SELECT DISTINCT klassenName FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . $this->bogenID . "'  ORDER BY LENGTH(klassenName), klassenName";
		}
		elseif(DB::getSession()->isMember("Webportal_Leistungsbericht_Admin")) {
			$addSQL = "";
			$isAdmin = true;
			$SQL = "SELECT DISTINCT klassenName FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . $this->bogenID . "'  ORDER BY LENGTH(klassenName), klassenName";
		}
		else {
			$addSQL = "AND klassenleitungUserID = '" . DB::getUserID() . "'";
			$SQL = "SELECT * FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . $this->bogenID . "' $addSQL ORDER BY LENGTH(klassenName), klassenName";
		}
		
		$klassen = DB::getDB()->query($SQL);
		
		$klassenHTML = "";
		
		while($klasse = DB::getDB()->fetch_array($klassen)) {
			if($isAdmin) $artKlassenleitung = "Administrator Beobachtungsbogen";
			elseif($isSchulleitung) $artKlassenleitung = "Schulleitung";
			elseif($klasse['klassenleitungTyp'] == 1) $artKlassenleitung = "1. Klassenleitung";
			elseif($klasse['klassenleitungTyp'] == 2) $artKlassenleitung = "2. Klassenleitung";
			else $artKlassenleitung = "Unbekannte Klassenleitung";
			
			$faecher = DB::getDB()->query("SELECT * FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='" .$this->bogenID . "' AND klasseName='" . $klasse['klassenName'] . "' ORDER BY fachName, lehrerKuerzel");
			
			$fachStatus = "";
			while($fach = DB::getDB()->fetch_array($faecher)) {
				$fachStatus .= "<tr><td>" . $fach['fachName'] . "</td><td>" . $fach['lehrerKuerzel'] . "</td><td>";
				if($fach['isOK'] == 1) {
					$fachStatus .= "<p class=\"text-green\"><i class=\"fa fa-check\"></i> Eingetragen</p></td></tr>";
				}
				else {
					$fachStatus .= "<p class=\"text-red\"><i class=\"fa fa-exclamation-triangle\"></i> Nicht Eingetragen</p></td></tr>";
				}
			}
			
			eval("\$klassenHTML .= \"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/index_bit") . "\";");
		}
		
		if($klassenHTML == "") $klassenHTML = "<tr><td colspan=\"4\"><center><strong>Sie sind bei keiner Klasse als Klassenleitung eingetragen</strong></center></td></tr>";
		
		eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/klassenleitung/index") . "\");");
		exit(0);
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
		return 'Klassenleiteransicht der Beobachtungsbögen';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function onlyForSchool() {
		return [];
	}

}


?>