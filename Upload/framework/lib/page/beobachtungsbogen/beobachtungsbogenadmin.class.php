<?php


class beobachtungsbogenadmin extends AbstractPage {

	private $stundenplan;
	
	public function __construct() {
		
		$this->needLicense = true;
		
		parent::__construct(
				[
						"Beobachtungsbogen",
						"Administration Beobachtungsbögen"
				]
		);
		
		$this->checkLogin();
		
		if(!DB::getSession()->isMember("Webportal_Leistungsbericht_Admin")) {
			new errorPage("Dieser Bereich ist nur für Administratoren der Beobachtungsbögen");
			exit(0);
		}
		
	}

	public function execute() {
		
		$this->stundenplan = stundenplandata::getCurrentStundenplan();
		
		if($_GET['addBogen'] > 0) {
			// Neuen Bogen anlegen
			if($_GET['doSave'] > 0) {
				// Bogen erstellen
				$stundenplan = new stundenplandata($_REQUEST['stundenplanID']);
				
				// Bogen anlegen
				DB::getDB()->query("INSERT INTO beobachtungsbogen_boegen 
						(
							beobachtungsbogenName,
							beobachtungsbogenDatum,
							beobachtungsbogenStartDate,
							beobachtungsbogenDeadline,
							beobachtungsbogenText,
							beobachtungsbogenTitel
						) values(
							'" . addslashes($_POST['beobachtungsbogenName'])      . "',
							'" . addslashes($_POST['beobachtungsbogenDatum'])     . "',
							'" . addslashes($_POST['beobachtungsbogenStartDate']) . "',
							'" . addslashes($_POST['beobachtungsbogenDeadline'])  . "',
							'" . addslashes($_POST['beobachtungsbogenText'])      . "',
							'" . addslashes($_POST['beobachtungsbogenTitel'])     . "'
						)
				");
				
				$newBogenID = DB::getDB()->insert_id();
				
				// Unterricht kopieren aus dem zugrundeliegenden Stundenplan
				$grades = klasse::getAllKlassen();
				
				for($i = 0; $i < sizeof($grades); $i++) {
					
					$istDabei = false;
					
					if($_POST['kl1_' . $grades[$i]->getKlassenName()] > 0) {
						// Klasse soll berücksichtigt werden.
						// Datensatz zur Klassenleitung anlegen
						DB::getDB()->query("INSERT INTO beobachtungsbogen_klassenleitung 
								
									(beobachtungsbogenID, klassenName, klassenleitungUserID, klassenleitungTyp) 
								values(
									'" . $newBogenID . "',
									'" . $grades[$i]->getKlassenName() . "',
									'" . DB::getDB()->escapeString($_POST['kl1_' . $grades[$i]->getKlassenName()]) . "',1
								)
						");
						
						if($_POST['kl2_' . $grades[$i]->getKlassenName()] > 0) {
							DB::getDB()->query("INSERT INTO beobachtungsbogen_klassenleitung
							
									(beobachtungsbogenID, klassenName, klassenleitungUserID, klassenleitungTyp)
								values(
									'" . $newBogenID . "',
									'" . $grades[$i]->getKlassenName() . "',
									'" . DB::getDB()->escapeString($_POST['kl2_' . $grades[$i]->getKlassenName()]) . "',2
								)
							");
						}
						
						$istDabei = true;
					}
					if($istDabei) {	
						// Lehrer der Klasse holen
						$zuordnungen = DB::getDB()->query("SELECT DISTINCT stundeLehrer, stundeFach FROM stundenplan_stunden WHERE stundenplanID='" . $stundenplan->getStundenplanID() . "' AND stundeKlasse LIKE '" . $grades[$i]->getKlassenName() . "%'");
						while($zuordnung = DB::getDB()->fetch_array($zuordnungen)) {
							if($zuordnung['stundeFach'] != "") {
								DB::getDB()->query("INSERT INTO beobachtungsbogen_klasse_fach_lehrer
									(beobachtungsbogenID, klasseName, fachName, lehrerKuerzel)
										values(
											'" . $newBogenID . "',
											'" . $grades[$i]->getKlassenName() . "',
											'" . $zuordnung['stundeFach'] . "',
											'" . $zuordnung['stundeLehrer'] . "'
										)
								");
							}
						}					
					}
				}
				
				// Fragen erstellen
				
				for($i = 1; $i <= 10; $i++) {
					if($_POST['frage_' . $i . '_frage'] != "") {
						// Frage erstellen
						DB::getDB()->query("INSERT INTO beobachtungsbogen_fragen
							(beobachtungsbogenID, frageText, frageTyp, frageZugriff)
								values(
									'" . $newBogenID . "',
									'" . addslashes($_POST['frage_' . $i . '_frage']) . "',
									'" . addslashes($_POST['frage_' . $i . '_typ']) . "',
									'" . addslashes($_POST['frage_' . $i . '_zugriff']) . "'
								)
						");
					}
				}
				
				header("Location: index.php?page=beobachtungsbogenadmin");
				exit(0);
			}
			else {
				// Formular anzeigen
				$gradeListe = $this->getGradeList(false);
				
				$optionsStundenplan = "";
				$plaene = stundenplandata::getAllCurrentPlans();
				
				for($i = 0; $i < sizeof($plaene); $i++) {
					$optionsStundenplan .= "<option value=\""
							. $plaene[$i]['stundenplanID'] . "\">"
							. $plaene[$i]['stundenplanName'] . " (ab " . functions::getFormatedDateFromSQLDate($plaene[$i]['stundenplanAb']) . ")</option>";
				}
				
				$fragen = "";
				
				$heute = date("Y-m-d");
				
				for($i = 1; $i <= 10; $i++) {
					$frage['frageText'] = "";
					$isTyp1 = "";
					$isTyp2 = "";
					$isLehrer = "";
					$isKlassenleitung = "";
					
					eval("\$fragen .= \"" . DB::getTPL()->get("beobachtungsbogen/admin/add_frage") . "\";");
				}
				
				eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/add") . "\");");
				exit(0);
			}
			
		}
		
		if($_GET['deleteBogen'] > 0) {
			if($_GET['confirmCodeSolution'] != "") {
				if($_GET['confirmCodeSolution'] == $_POST['confirmCode']) {
					DB::getDB()->query("DELETE FROM beobachtungsbogen_boegen WHERE beobachtungsbogenID='{$_GET['deleteBogen']}'");
					DB::getDB()->query("DELETE FROM beobachtungsbogen_fragen_daten WHERE frageID IN (SELECT frageID FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='{$_GET['deleteBogen']}')");					
					DB::getDB()->query("DELETE FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='{$_GET['deleteBogen']}'");
					DB::getDB()->query("DELETE FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='{$_GET['deleteBogen']}'");
					DB::getDB()->query("DELETE FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='{$_GET['deleteBogen']}'");
						
					header("Location: index.php?page=beobachtungsbogenadmin");
					exit(0);
					
				}
				else {
					new error("Der Code stimmt nicht!");
					exit(0);
				}					
			}
			else {
				// Confirmcode generieren:
				$confirmCodeTemp = md5(rand());
				
				$start = rand(3,28);
				$laenge = rand(5,9);
				
				$confirmCodeComplete = substr($confirmCodeTemp, 0, $start-1) . "<u>" . substr($confirmCodeTemp, $start, $laenge) . "</u>" . substr($confirmCodeTemp, $start+$laenge);
				
				$confirmCodeSolution=substr($confirmCodeTemp, $start, $laenge);
				
				$bogen = DB::getDB()->query_first("SELECT * FROM beobachtungsbogen_boegen WHERE beobachtungsbogenID='" . intval($_GET['deleteBogen']) . "'");
				eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/deletebogen") . "\");");
				
				exit(0);
			}
		}
		
		if($_GET['editMatrix'] > 0) {
			// uuuuund los:
			
			$bogen = DB::getDB()->query_first("SELECT * FROM beobachtungsbogen_boegen WHERE beobachtungsbogenID='" . intval($_GET['editMatrix']) . "'");
			if($bogen['beobachtungsbogenID'] == "") {
				new error("Der angegebene Beobachtungsbogen existiert nicht!");
				exit(0);
			}
			
			if($_GET['deleteUnterricht'] > 0) {
				DB::getDB()->query("DELETE FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='" . $_GET['editMatrix'] . "'
						AND klasseName='" . $_GET['klasseName'] . "'
						AND fachName='" . $_GET['fachName'] . "'
						AND lehrerKuerzel='" . $_GET['lehrerKuerzel'] . "'
						");
				header("Location: index.php?page=beobachtungsbogenadmin&editMatrix=" . $_GET['editMatrix']);
				exit();
			}
			
			if($_GET['addUnterricht']) {
				DB::getDB()->query("INSERT INTO beobachtungsbogen_klasse_fach_lehrer
						(beobachtungsbogenID, klasseName, fachName, lehrerKuerzel)
						values(
							'" . intval($_GET['editMatrix']) . "',
							'" . addslashes($_POST['klasseName']) . "',
							'" . addslashes($_POST['fachName']) . "',
							'" . addslashes($_POST['lehrerKuerzel']) . "'
						)");
				header("Location: index.php?page=beobachtungsbogenadmin&editMatrix=" . $_GET['editMatrix']);
				exit();
			}
			
			
			$optionsKlasse = "";
			$allPossibleGrades = grade::getAllGrades();
			for($i = 0; $i < sizeof($allPossibleGrades); $i++) {
				$optionsKlasse .= "<option value=\"" . $allPossibleGrades[$i] . "\">" . $allPossibleGrades[$i] . "</option>\n";
			}
			
			$optionsFach = "";
			$allPossible = stundenplandata::getCurrentStundenplan()->getAll("subject");
			for($i = 0; $i < sizeof($allPossible); $i++) {
				$optionsFach .= "<option value=\"" . $allPossible[$i] . "\">" . $allPossible[$i] . "</option>\n";
			}
			
			$optionsLehrer = "";
			$allPossible = stundenplandata::getCurrentStundenplan()->getAll("teacher");
			for($i = 0; $i < sizeof($allPossible); $i++) {
				$optionsLehrer .= "<option value=\"" . $allPossible[$i] . "\">" . $allPossible[$i] . "</option>\n";
			}
			
			
			$allData = DB::getDB()->query("SELECT * FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='" . $_GET['editMatrix'] . "' ORDER BY LENGTH(klasseName), klasseName, fachName");
			
			$data = array();
			$alleKlassen = array();
			$alleFaecher = array();
			
			while($d = DB::getDB()->fetch_array($allData)) {
				$data[$d['klasseName']][$d['fachName']][] = $d['lehrerKuerzel'];
				
				if(!in_array($d['fachName'], $alleFaecher)) $alleFaecher[] = $d['fachName'];
				if(!in_array($d['klasseName'], $alleKlassen)) $alleKlassen[] = $d['klasseName'];
			}
			
			sort($alleFaecher);
			
			$THEMATRIX = "<tr><th>Fach | Klasse</th>";
			for($i = 0; $i < sizeof($alleKlassen); $i++) {
				$THEMATRIX .= "<th>" . $alleKlassen[$i] . "</th>";
			}
			
			for($f = 0; $f < sizeof($alleFaecher); $f++) {
				$fach = $alleFaecher[$f];
				
				$THEMATRIX .= "<tr><td>" . $fach . "</td>";
				
				for($k = 0; $k < sizeof($alleKlassen); $k++) {
					$klasse = $alleKlassen[$k];
					if(is_array($data[$klasse][$fach])) {
						$THEMATRIX .= "<td>";
						for($i = 0; $i < sizeof($data[$klasse][$fach]); $i++) {
							$THEMATRIX .= $data[$klasse][$fach][$i];
							$THEMATRIX .= " <a href=\"index.php?page=beobachtungsbogenadmin&editMatrix=" . $_GET['editMatrix'] . "&deleteUnterricht=1&klasseName=" . urlencode($klasse) . "&fachName=" . urlencode($fach) . "&lehrerKuerzel=" . urlencode($data[$klasse][$fach][$i]) . "\"><i class=\"fa fa-trash\"></i></a><br />";
						}
						$THEMATRIX .= "</td>";
					}
					else {
						$THEMATRIX .= "<td>" . "&nbsp;" . "</td>";
					}
				}
				
				$THEMATRIX .= "</tr>";
			}
			
			$THEMATRIX .= "</tr>";
			
			
			eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/matrix") . "\");");
			exit(0);
			
			
		}
		
		if($_GET['editBogen'] > 0) {
			$bogenID = intval($_GET['editBogen']);
			if($_GET['doSave'] > 0) {
				
				$bogen = DB::getDB()->query_first("SELECT * FROM beobachtungsbogen_boegen WHERE beobachtungsbogenID='" . intval($_GET['editBogen']) . "'");
				
					
				// Klassen
				$klassen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . intval($_GET['editBogen']) . "'");
					
				$klData = array();
				while($k = DB::getDB()->fetch_array($klassen)) {
					if($k['klassenleitungTyp'] == 1) $klData[$k['klassenName']]['kl1'] = $k['klassenleitungUserID'];
					if($k['klassenleitungTyp'] == 2) $klData[$k['klassenName']]['kl2'] = $k['klassenleitungUserID'];
				}
					
				$grades = klasse::getAllKlassen();
				
				$stundenplan = $this->stundenplan;

				$gradeListe = "";
				for($i = 0; $i < sizeof($grades); $i++) {
					if($_POST['kl1_'.$grades[$i]->getKlassenName()] > 0) {
						if($klData[$grades[$i]->getKlassenName()]['kl1'] > 0) {
							// Schon vorhanden --> Update
							DB::getDB()->query("UPDATE beobachtungsbogen_klassenleitung SET klassenleitungUserID='" . $_POST['kl1_'.$grades[$i]->getKlassenName()] . "' WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "' AND klassenName='" . $grades[$i]->getKlassenName() . "' AND klassenleitungTyp=1");
							if($klData[$grades[$i]->getKlassenName()]['kl2'] != "") {
								if($_POST['kl1_'.$grades[$i]->getKlassenName()] < 0) {
									DB::getDB()->query("DELETE FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "' AND klassenName='" . $grades[$i] . "' AND klassenleitungTyp=2");
								}
								else {
									DB::getDB()->query("UPDATE beobachtungsbogen_klassenleitung SET klassenleitungUserID='" . $_POST['kl2_'.$grades[$i]->getKlassenName()] . "' WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "' AND klassenName='" . $grades[$i]->getKlassenName() . "' AND klassenleitungTyp=2");
								}
							}
						}
						else {
							// Noch nicht vorhanden. --> Klassenunterricht in die Matrix kopieren
							// Lehrer der Klasse holen
							$zuordnungen = DB::getDB()->query("SELECT DISTINCT stundeLehrer, stundeFach FROM stundenplan_stunden WHERE stundenplanID='" . $stundenplan->getStundenplanID() . "' AND stundeKlasse LIKE '" . $grades[$i]->getKlassenName() . "%'");
							while($zuordnung = DB::getDB()->fetch_array($zuordnungen)) {
								if($zuordnung['stundeFach'] != "") {
									DB::getDB()->query("INSERT INTO beobachtungsbogen_klasse_fach_lehrer
									(beobachtungsbogenID, klasseName, fachName, lehrerKuerzel)
										values(
											'" . $_GET['editBogen'] . "',
											'" . $grades[$i]->getKlassenName() . "',
											'" . $zuordnung['stundeFach'] . "',
											'" . $zuordnung['stundeLehrer'] . "'
										)
								");
								}
							}
							DB::getDB()->query("INSERT INTO beobachtungsbogen_klassenleitung
							
									(beobachtungsbogenID, klassenName, klassenleitungUserID, klassenleitungTyp)
								values(
									'" . $_GET['editBogen'] . "',
									'" . $grades[$i]->getKlassenName() . "',
									'" . $_POST['kl1_' . $grades[$i]->getKlassenName()] . "',1
								)
								");
							
							if($_POST['kl2_' . $grades[$i]->getKlassenName()] > 0) {
								DB::getDB()->query("INSERT INTO beobachtungsbogen_klassenleitung
				
									(beobachtungsbogenID, klassenName, klassenleitungUserID, klassenleitungTyp)
								values(
									'" . $_GET['editBogen'] . "',
									'" . $grades[$i]->getKlassenName() . "',
									'" . $_POST['kl2_' . $grades[$i]] . "',2
								)
								");
							}
						}
					}
					else {
						// Einträge löschen
						DB::getDB()->query("DELETE FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "' AND klassenName='" . $grades[$i]->getKlassenName() . "' ");
						DB::getDB()->query("DELETE FROM beobachtungsbogen_klasse_fach_lehrer WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "' AND klasseName='" . $grades[$i]->getKlassenName() . "' ");
					}
				}
					
					
				// Fragen zusammenstellen
				$fragenData = array();
				$fragenData[] = array(); // index 0 leer füllen
				$fragen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='$bogenID'");
				while($f = DB::getDB()->fetch_array($fragen)) {
					$fragenData[] = $f;
				}
					
					
				$fragen = "";
					
				for($i = 1; $i <= 10; $i++) {
					if(is_array($fragenData[$i])) {
						if($_POST['frage_' . $i . '_frage'] == "") {
							// Frage löschen
							DB::getDB()->query("DELETE FROM beobachtungsbogen_fragen WHERE frageID='" . $fragenData[$i]['frageID'] . "'");
						}
						else {
							// Update
							DB::getDB()->query("UPDATE beobachtungsbogen_fragen SET frageText='" . addslashes($_POST['frage_' . $i . '_frage']) . "', frageTyp='" . $_POST['frage_' . $i . '_typ'] . "', frageZugriff='" . $_POST['frage_' . $i . '_zugriff'] . "' WHERE frageID='" . $fragenData[$i]['frageID'] . "'");
						}
					}
					else {
						// Noch nicht vorhanden
						if($_POST['frage_' . $i . '_frage'] != "") {
							// Frage anlegen
							DB::getDB()->query("INSERT INTO beobachtungsbogen_fragen
							(beobachtungsbogenID, frageText, frageTyp, frageZugriff)
								values(
									'" . $_GET['editBogen'] . "',
									'" . addslashes($_POST['frage_' . $i . '_frage']) . "',
									'" . addslashes($_POST['frage_' . $i . '_typ']) . "',
									'" . addslashes($_POST['frage_' . $i . '_zugriff']) . "'
								)
							");
						}
					}
				}
				// die();
				
				// Daten vom Bogen selbst aktualisieren
				DB::getDB()->query("UPDATE beobachtungsbogen_boegen SET
						beobachtungsbogenName='" . addslashes($_POST['beobachtungsbogenName']) . "',
						beobachtungsbogenDatum='" . addslashes($_POST['beobachtungsbogenDatum']) . "',
						beobachtungsbogenStartDate='" . addslashes($_POST['beobachtungsbogenStartDate']) . "',
						beobachtungsbogenDeadline='" . addslashes($_POST['beobachtungsbogenDeadline']) . "',
						beobachtungsbogenTitel='" . addslashes($_POST['beobachtungsbogenTitel']) . "',
						beobachtungsbogenText='" . addslashes($_POST['beobachtungsbogenText']) . "'
						WHERE beobachtungsbogenID='" . $_GET['editBogen'] . "'
				");
				
				header("Location: index.php?page=beobachtungsbogenadmin&editBogen=" . $_GET['editBogen'] . "&saved=1");
				exit(0);
			}
			
			$bogen = DB::getDB()->query_first("SELECT * FROM beobachtungsbogen_boegen WHERE beobachtungsbogenID='" . intval($_GET['editBogen']) . "'");
		
			
			// Klassen
			$klassen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_klassenleitung WHERE beobachtungsbogenID='" . intval($_GET['editBogen']) . "'");
			
			$klData = array();
			while($k = DB::getDB()->fetch_array($klassen)) {
				if($k['klassenleitungTyp'] == 1) $klData[$k['klassenName']]['kl1'] = $k['klassenleitungUserID'];
				if($k['klassenleitungTyp'] == 2) $klData[$k['klassenName']]['kl2'] = $k['klassenleitungUserID'];
			}
			
			// Debugger::debugObject($klData);
			
			$grades = klasse::getAllKlassen();
			
			$gradeListe = "";
			for($i = 0; $i < sizeof($grades); $i++) {
				$gradeListe .= "<tr><td>" . $grades[$i]->getKlassenName() . "</td>";
				$gradeListe .= "<td><select name=\"kl1_" . $grades[$i]->getKlassenName() . "\" class=\"form-control\"><option value=\"-1\">--</option>" . $this->getTeacherSelect($grades[$i]->getKlassenName(), 1, $klData[$grades[$i]->getKlassenName()]['kl1']) . "</select></td>";
				$gradeListe .= "<td><select name=\"kl2_" . $grades[$i]->getKlassenName()  . "\" class=\"form-control\"><option value=\"-1\">--</option>" . $this->getTeacherSelect($grades[$i]->getKlassenName(), 2, $klData[$grades[$i]->getKlassenName()]['kl2']) . "</select></td>";
				$gradeListe .= "</tr>";
			}
			
			
			// Fragen zusammenstellen
			$fragenData = array();
			$fragenData[] = array(); // index 0 leer füllen
			$fragen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_fragen WHERE beobachtungsbogenID='$bogenID'");
			while($f = DB::getDB()->fetch_array($fragen)) {
				$fragenData[] = $f;
			}
			
			
			$fragen = "";
			
			for($i = 1; $i <= 10; $i++) {
				if(is_array($fragenData[$i])) {
					$frage['frageText'] = "";
					$isTyp1 = "";
					$isTyp2 = "";
					$isLehrer = "";
					$isKlassenleitung = "";
					$frage['frageText'] = $fragenData[$i]['frageText'];
					if($fragenData[$i]['frageTyp'] == 1) $isTyp1 = " selected=\"selected\"";
					if($fragenData[$i]['frageTyp'] == 2) $isTyp2 = " selected=\"selected\"";
					if($fragenData[$i]['frageZugriff'] == "LEHRER") $isLehrer = " selected=\"selected\"";
					if($fragenData[$i]['frageZugriff'] == "KLASSENLEITUNG") $isKlassenleitung = " selected=\"selected\"";
				}
				else {
					$frage['frageText'] = "";
					$isTyp1 = "";
					$isTyp2 = "";
					$isLehrer = "";
					$isKlassenleitung = "";
				}
					
				eval("\$fragen .= \"" . DB::getTPL()->get("beobachtungsbogen/admin/add_frage") . "\";");
			}
			
			eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/edit") . "\");");
			exit(0);
		}
		
		if($_GET['editNachfristen'] > 0) {
			if($_GET['deleteFrist'] > 0) {
				DB::getDB()->query("DELETE FROM beobachtungsbogen_eintragungsfrist WHERE userID='" . intval($_GET['deleteFrist']) . "' AND beobachtungsbogenID='" . intval($_GET['editNachfristen']) . "'");
			}
			
			if($_GET['addFrist'] > 0) {
				DB::getDB()->query("INSERT INTO beobachtungsbogen_eintragungsfrist (beobachtungsbogenID, userID, frist) values('" . intval($_GET['editNachfristen']) . "','" . intval($_POST['userID']) . "','" . $_POST['frist'] . "')");
			}
			
			$fristenHTML = "";
			
			$fristen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_eintragungsfrist NATURAL JOIN users WHERE beobachtungsbogenID='" . intval($_GET['editNachfristen']) . "' ORDER BY userLastName, userFirstName");
			
			while($frist = DB::getDB()->fetch_array($fristen)) {
				$fristenHTML .= "<tr><td>" . $frist['userLastName'] . ", " . $frist['userFirstName'] . "</td><td>" . functions::getFormatedDateFromSQLDate($frist['frist']) . "</td><td>";
				
				
				$fristenHTML .= "<a href=\"index.php?page=beobachtungsbogenadmin&editNachfristen=" . $_GET['editNachfristen'] . "&deleteFrist=" . $frist['userID'] . "\"><i class=\"fa fa-trash\"></i> Löschen</a>";
			}
			
			$selects = DB::getDB()->query("SELECT * FROM users WHERE userID in (SELECT lehrerUserID FROM lehrer) AND userID NOT IN (SELECT userID FROM beobachtungsbogen_eintragungsfrist WHERE beobachtungsbogenID='" . $_GET['editNachfristen'] . "') ORDER BY userLastName, userFirstName");
			
			$optionsLehrer = "";
			while($u = DB::getDB()->fetch_array($selects)) {
				$optionsLehrer .= "<option value=\"" . $u['userID'] . "\">" . $u['userLastName'] . ", " . $u['userFirstName'] . "</option>\n";
			}
			
			eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/nachfrist") . "\");");
			exit(0);
		}
		
		$currentBoegen = DB::getDB()->query("SELECT * FROM beobachtungsbogen_boegen ORDER BY beobachtungsbogenDatum DESC");
		
		$bogenHTML = "";
		while($bogen = DB::getDB()->fetch_array($currentBoegen)) {
			eval("\$bogenHTML .= \"" . DB::getTPL()->get("beobachtungsbogen/admin/bit") . "\";");
		}
		
		if($bogenHTML == "") $bogenHTML = "<tr><td colspan=\"7\" style=\"text-align: center\"><strong><i class=\"fa fa-ban\"></i> Keiner vorhanden</strong></td></tr>";
		
		eval("echo(\"" . DB::getTPL()->get("beobachtungsbogen/admin/index") . "\");");
	}
	
	
	public static function getNotifyItems() {
		return array();
	}
	
	private function getGradeList($widthTakeFromStundenplan=false) {
		$result = "";
		
		$grades = klasse::getAllKlassen();
		
		for($i = 0; $i < sizeof($grades); $i++) {
			$result .= "<tr><td>" . $grades[$i]->getKlassenName() . "</td>";
			$result .= "<td><select name=\"kl1_" . $grades[$i]->getKlassenName() . "\" class=\"form-control\"><option value=\"-1\">--</option>" . $this->getTeacherSelect($grades[$i]->getKlassenName(),1) . "</select></td>";
			$result .= "<td><select name=\"kl2_" . $grades[$i]->getKlassenName() . "\" class=\"form-control\"><option value=\"-1\">--</option>" . $this->getTeacherSelect($grades[$i]->getKlassenName(),2) . "</select></td>";
			$result .= "</tr>";
		}
		
		return $result;
	}
	
	private $teachers = array();
	
	private function getTeacherSelect($klassenname="", $klassenleitungNummer=1, $preselect='NOPRESELECT') {
		if(sizeof($this->teachers) == 0) {
			$this->teachers = lehrer::getAll();
		}
		
		$html = "";
		
		$klasse = null;
		if($klassenname != "") {
			$klasse = klasse::getByName($klassenname);
		}
			
		for($i = 0; $i < sizeof($this->teachers); $i++) {
			
			
			if($preselect == 'NOPRESELECT') {
				
				$isKL = false;
				
			
				if($klasse != null) {
					if($klasse->isKlassenLeitung($this->teachers[$i])) {
						if($klassenleitungNummer == 1) {
							if($klasse->isFirstKlassenleitung($this->teachers[$i])) {
								$isKL = true;
							}
						}
						else {
							if(!$klasse->isFirstKlassenleitung($this->teachers[$i])) {
								$isKL = true;
							}
						}
					}
				}
				
				$html .= "<option value=\"" . $this->teachers[$i]->getUserID() . "\"" . ($isKL ? (" selected=\"selected\"") : ("")) . ">" . $this->teachers[$i]->getName() . ", " . $this->teachers[$i]->getRufname() . " (" . $this->teachers[$i]->getKuerzel() . ")</option>";
				
			}
			else {
				$html .= "<option value=\"" . $this->teachers[$i]->getUserID() . "\"" . ($this->teachers[$i]->getUserID() == $preselect ? (" selected=\"selected\"") : ("")) . ">" . $this->teachers[$i]->getName() . ", " . $this->teachers[$i]->getRufname() . " (" . $this->teachers[$i]->getKuerzel() . ")</option>";
			}
			
		}			
		
		return $html;
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
		return 'Administration der Beobachtungsbögen';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array(
				array(
						'groupName' => 'Webportal_Leistungsbericht_Admin',
						'beschreibung' => 'Administrator für den Beobachtungsbogen'
				)
		);
	
	}
	
	public static function onlyForSchool() {
		return [];
	}

}


?>