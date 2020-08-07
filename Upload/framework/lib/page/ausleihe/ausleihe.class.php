<?php

class ausleihe extends AbstractPage {

	private $info;
	
	private $isAdmin = false;
		
	private $displayName = "";
	

	public function __construct() {
	    parent::__construct(array("Reservierungen", "Ausleihe"));
		
		$this->checkLogin(); 
				
		$this->displayName = self::hasCurrentUserAccess();

		
		if($this->displayName == NULL) {
			new errorPage();
		}
		
		
		if(in_array("Webportal_Reservierung_Admin",DB::getSession()->getGroupNames()) || DB::getSession()->isAdmin()) $this->isAdmin = true;
	}
	
	/**
	 * Überprüft, ob der Nutzer Zugriff hat.
	 * (Ergibt NULL, wenn kein Zugriff. Ansonsten der Name, der inm der Ausleihe angezeigt wird.)
	 * @return String|NULL
	 */
	
	public static function hasCurrentUserAccess() {
		
		$displayName = "";
		
		if(DB::getSession()->isTeacher() && DB::getSettings()->getBoolean("ausleihe-lehrer")) {
			$access = true;
			$displayName = DB::getSession()->getTeacherObject()->getKuerzel();
		}
		
		
		if(DB::getSession()->isPupil() && DB::getSettings()->getBoolean("ausleihe-schueler")) {
			$access = true;
			$displayName = DB::getSession()->getPupilObject()->getCompleteSchuelerName() . " (" . DB::getSession()->getPupilObject()->getKlasse() . ")";
		}
		
		if(DB::getSession()->isEltern() && DB::getSettings()->getBoolean("ausleihe-eltern")) {
			$access = true;
		 	$displayName= DB::getSession()->getData("userName");
		}
		
		if(!$access) {
			if(DB::getSession()->isMember('Webportal_Ausleihe_Nicht_Schulperson')) {
				$access = true;
				$displayName = DB::getSession()->getData("userName");
			}
		}
		
		if(!$access && DB::getSession()->isAdmin()) {
			$access = true;
				
			$displayName = DB::getSession()->getData("userName");
		}
		
		
		if($access) return $displayName;
		
		else return NULL;
	}

	public function execute() {
		
		if(isset($_REQUEST['objektID'])) {
			
			$objekt = DB::getDB()->query_first("SELECT * FROM ausleihe_objekte WHERE isActive=1 AND objektID='" . addslashes($_REQUEST['objektID']) . "'");
			
			// firstDay = TTMMYYYY
			if(!isset($_REQUEST['firstDay'])) {
				$thisweek = strtotime("this week");
				
				if(date("w") == 6 || date("w") == 0) {
					$thisweek += (7*24*60*60) - 100;
					$informNextWeek = true;
				}
				else $informNextWeek = false;
				
				$firstDayWeek = date("j", $thisweek);
				$monthFirstDayWeek = date("n", $thisweek);
				$yearFirstDayWeek = date("Y", $thisweek);
				$_REQUEST['firstDay'] = date("dmY",$thisweek);
			}
			else {
				$firstDayWeekTime = mktime(10,10,10,
						substr($_REQUEST['firstDay'], 2,2),
						substr($_REQUEST['firstDay'], 0,2),
						substr($_REQUEST['firstDay'], 4,4));
				$firstDayWeek = date("j", $firstDayWeekTime);
				$monthFirstDayWeek = date("n", $firstDayWeekTime);
				$yearFirstDayWeek = date("Y", $firstDayWeekTime);
			}
			
			if($objekt['objektID'] > 0) {
				if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
				else $action = "index";
					
				switch($action) {
					case "index":
					default:
						$html = $this->showWeekForObject($firstDayWeek, $monthFirstDayWeek, $yearFirstDayWeek, $objekt);
						
						eval("\$content = \"" . DB::getTPL()->get("ausleihe/ausleihe_weekview") . "\";");
					break;
					
					case "save":
						if($_POST['ausleihe_klasse'] != "") {
							$this->makeWeekReservations($firstDayWeek, $monthFirstDayWeek, $yearFirstDayWeek, $objekt);
							header("Location: index.php?page=ausleihe&objektID=" . $_REQUEST['objektID'] . "&firstDay=" . $_REQUEST['firstDay'] . "&gplsession=" . $_REQUEST['gplsession']);
							exit(0);
						}
						else {
							header("Location: index.php?page=ausleihe&objektID=" . $_REQUEST['objektID'] . "&firstDay=" . $_REQUEST['firstDay'] . "&gplsession=" . $_REQUEST['gplsession']);
							exit(0);
						}
					break;
					
					case "delete":
						$datum = addslashes($_GET['ausleiheDatum']);
						$stunde = intval($_GET['ausleiheStunde']);
						$objektNummer = intval($_GET['objektNummer']);
						$o = DB::getDB()->query_first("SELECT * FROM ausleihe_ausleihe WHERE
									ausleiheDatum='$datum' AND
									ausleiheObjektID='" . $objekt['objektID'] . "' AND
									ausleiheObjektIndex='$objektNummer' AND
									ausleiheStunde='$stunde'
								");
						if($o['ausleiheID'] > 0) {
							// Schon mal vorhanden.
							if(strtolower($o['ausleiheLehrer']) == strtolower($this->displayName) || $this->isAdmin) {
								// Löschen
								DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheID='" . $o['ausleiheID'] . "'");
								header("Location: index.php?page=ausleihe&objektID=" . $_REQUEST['objektID'] . "&firstDay=" . $_REQUEST['firstDay'] . "&gplsession=" . $_REQUEST['gplsession']);
								exit(0);
							}
						}
						else {
							die("Database violation. (Reservation not found. Error 897)");
						}
					break;
				}
			}
			else {
				die("Malformed Request: objektID invalid or not active.");
			}
		}
		else {
		
			$content = "Hier können Objekte reserviert werden.";
		
		}
				
		eval("echo(\"" .DB::getTPL()->get("ausleihe/ausleihe_index") . "\");");
	}

	
	private function makeWeekReservations($firstDayWeek, $monthFirstDayWeek, $yearFirstDayWeek, $objekt) {
		// $objekt ist Array
		$timeFirstDayWeek = mktime(0,0,0,$monthFirstDayWeek,$firstDayWeek, $yearFirstDayWeek);
		$secPerDay = 24 * 60 * 60 + 2; // Schaltsekunden umgehen, es geht uns ja nur um die Tage, nicht um die Zeit.
	
		if(date("w",$firstDayWeek) != 4) die("Falsches Datumsformat! Es muss ein Montag angegeben werden: " . date("w",$firstDayWeek));
	
		$showStunden = 10;

		$dates = array();
		$datumDisplay = array();
	
		for($i = 0; $i < 5; $i++) {
			$timeDay = $timeFirstDayWeek + ($i * $secPerDay);
			$dates[] = "'" . date("Y-m-d",$timeDay) . "'";
			$datumDisplay[] = date("d.m.Y",$timeDay);
			$datesAusleiheData[] = date("Y-m-d",$timeDay);
		}
	
		$ausleihData = array();
		if(sizeof($dates) > 0) {
			$ausleihen = DB::getDB()->query("SELECT * FROM ausleihe_ausleihe WHERE ausleiheObjektID='" . $objekt['objektID'] . "' AND ausleiheDatum IN (" . implode(",",$dates) . ")");
			while($a = DB::getDB()->fetch_array($ausleihen)) {
				$ausleihData[$a['ausleiheDatum']][$a['ausleiheStunde']][$a['ausleiheObjektIndex']] = $a;
			}
		}
	
		for($s = 1; $s <= $showStunden; $s++) {
				
			for($i = 0; $i < sizeof($datesAusleiheData); $i++) {
				for($v = 1; $v <= $objekt['objektAnzahl']; $v++) {
					if(!($ausleihData[$datesAusleiheData[$i]][$s][$v]['ausleiheID'] > 0)) {
						if(isset($_POST[$datesAusleiheData[$i] . "-" . $s . "-" . $v]) && $_POST[$datesAusleiheData[$i] . "-" . $s . "-" . $v] > 0) {
							DB::getDB()->query("INSERT INTO ausleihe_ausleihe
									(
										ausleiheObjektID,
										ausleiheObjektIndex,
										ausleiheDatum,
										ausleiheLehrer,
										ausleiheStunde,
										ausleiheKlasse
									) 
									values(
										'" . $objekt['objektID'] . "',
										'" . $v . "',
										'" . $datesAusleiheData[$i] . "',
										'" . $this->displayName . "',
										'" . $s . "',
										'" . addslashes($_POST['ausleihe_klasse']) . "'
									)
							");
						}
					}
				}
			}

		}
	}
	
	private function showWeekForObject($firstDayWeek, $monthFirstDayWeek, $yearFirstDayWeek, $objekt) {
		// $objekt ist Array
		$timeFirstDayWeek = mktime(12,0,0,$monthFirstDayWeek,$firstDayWeek, $yearFirstDayWeek);
		$secPerDay = 24 * 60 * 60 + 20; // Schaltsekunden umgehen, es geht uns ja nur um die Tage, nicht um die Zeit.
		
		if(date("w",$firstDayWeek) != 4) die("Falsches Datumsformat! Es muss ein Montag angegeben werden: " . date("w",$firstDayWeek));
		
		$html = "<table border=\"0\" style=\"width:100%\"><tr><td colspan=\"3\" align=\"center\"><h3>Woche vom " . date("d.m.Y",$timeFirstDayWeek) . "</h3></td></tr>";
		$prevWeek = $timeFirstDayWeek + 20;
		$prevWeek -= 7 * $secPerDay;
		
		$nextWeek = $timeFirstDayWeek + 20;
		$nextWeek += 7 * $secPerDay;
		
		$showStunden = 10;
		
		
		
		$html .= "<tr><td><a href=\"index.php?page=ausleihe&objektID={$objekt['objektID']}&firstDay=" . date("dmY", $prevWeek) . "&gplsession={$_REQUEST['gplsession']}\"><i class=\"fa fa-arrow-left\"></i> Woche zurück</a></td>";
		$html .= "<td align=\"center\"><a href=\"index.php?page=ausleihe&objektID={$objekt['objektID']}&gplsession={$_REQUEST['gplsession']}\"><i class=\"fa fa-calendar\"></i> Zur aktuellen Woche</a></td>";
		$html .= "<td align=\"right\"><a href=\"index.php?page=ausleihe&objektID={$objekt['objektID']}&firstDay=" . date("dmY", $nextWeek) . "&gplsession={$_REQUEST['gplsession']}\">Woche weiter <i class=\"fa fa-arrow-right\"></i></a></td></tr><tr><td colspan=\"3\">";
		
		$dates = array();
		$datumDisplay = array();
		
		for($i = 0; $i < 5; $i++) {
			$timeDay = $timeFirstDayWeek + ($i * $secPerDay);
			$dates[] = "'" . date("Y-m-d",$timeDay) . "'";
			$datumDisplay[] = date("d.m.Y",$timeDay);
			$datesAusleiheData[] = date("Y-m-d",$timeDay);
		}
		
		$ausleihData = array();
		if(sizeof($dates) > 0) {
			$ausleihen = DB::getDB()->query("SELECT * FROM ausleihe_ausleihe WHERE ausleiheObjektID='" . $objekt['objektID'] . "' AND ausleiheDatum IN (" . implode(",",$dates) . ")");
			while($a = DB::getDB()->fetch_array($ausleihen)) {
				$ausleihData[$a['ausleiheDatum']][$a['ausleiheStunde']][$a['ausleiheObjektIndex']] = $a;
			}		
		}

		$html .= "<table style=\"border-collapse: collapse; border: 1px solid black;width:100%\">";
		$html .= "<tr><td valign=\"center\" align=\"center\">Stunde</td>";
		for($i = 0; $i < sizeof($datumDisplay); $i++) {
			if($objekt['objektAnzahl'] > 1) {
				
				$anzahl = $objekt['objektAnzahl'];
				
				if( $objekt['objektAnzahl'] > 1 && $objekt['sumItems'] > 0) {
					$anzahl = $objekt['objektAnzahl'] / $objekt['sumItems'];
				}
				
				$colspan = " colspan=\"{$anzahl}\"";
			}
			else $colspan = "";
			$html .= "<td style=\" border: 0px solid black;border-left-width: 3px;\"align=center$colspan>" . $this->getDayName($i) . "<br /><small>" . $datumDisplay[$i] . "</small></td>";
		}
		$html .= "</tr>";
		
		$html .= "<tr><td style=\" border: 1px solid black;\">&nbsp;</td>";
		for($i = 0; $i < sizeof($datumDisplay); $i++) {
			
			$anzahl = $objekt['objektAnzahl'];
				
			if( $objekt['objektAnzahl'] > 1 && $objekt['sumItems'] > 0) {
				$anzahl = $objekt['objektAnzahl'] / $objekt['sumItems'];
			}
			
			
			for($v = 1; $v <= $anzahl; $v++) {
				if($objekt['sumItems'] > 0) {
					$beschriftung = "";
					$first = true;
					for($b = ($v * $objekt['sumItems']) - ($objekt['sumItems']-1); $b <= $v * $objekt['sumItems']; $b++) {
						
						if($first) {
							$beschriftung .= "";
							$first = false;
						}
						else $beschriftung .= ", ";
						
						$beschriftung .= '('.$b.')';
					}
				}
				else {
					$beschriftung = '('.$v.')';
				}
				$html .= "<td align=\"center\" style=\" border: 1px solid black;" . (($v == 1) ? ("border-left-width: 3px;") : ("")) . "\">" . $objekt['objektName'] . " $beschriftung</td>";
			}
		}
		$html .= "</tr>";
		
		$bgColor = "#CECECE";
		for($s = 1; $s <= $showStunden; $s++) {
			$html .= "<tr style=\"background-color: $bgColor;\" height=\"40\"><td style=\"border: 0px solid black;border-bottom-width:2px;vertical-align: middle;\" align=\"center\">$s</td>";
			
			for($i = 0; $i < sizeof($datesAusleiheData); $i++) {
				// Ferien prüfen
				// $ferien = DB::getDB()->query_first("SELECT * FROM kalender_ferien WHERE ferienStart<='" . $datesAusleiheData[$i] . "' AND ferienEnde >= '" . $datesAusleiheData[$i] . "'");


                // TODO wieder raus nehmen:
                $ferien['ferienID'] = 0;


				if($ferien['ferienID'] > 0 && $s == 1) {
					$html .= "<td rowspan=\"" . $showStunden . "\" colspan=\"" . $anzahl . "\" align=\"center\" style=\"vertical-align: middle;\"><i class=\"fa fa-pagelines\"></i> <b>" . $ferien['ferienName'] . "</b></td>";
				}
				elseif($ferien['ferienID'] > 0) {
					// Nix
				}
				else {
					$anzahl = $objekt['objektAnzahl'];
					
					if($objekt['objektAnzahl'] > 1 && $objekt['sumItems'] > 0) {
						$anzahl = $objekt['objektAnzahl'] / $objekt['sumItems'];	
					}
					
					for($v = 1; $v <= $anzahl; $v++) {
						if($ausleihData[$datesAusleiheData[$i]][$s][$v]['ausleiheID'] > 0) {
							$html .= "<td align=\"center\" style=\"background-color: $bgColor; border: 1px solid black;border-bottom-width:2px;" . (($v == 1) ? ("border-left-width: 3px;") : ("border-left-style: dotted;")) . "vertical-align: middle;\" valign=\"middle\"><span style=\"color:#FF1010\">" .
							$ausleihData[$datesAusleiheData[$i]][$s][$v]['ausleiheLehrer'] .
							"<br />" .
							$ausleihData[$datesAusleiheData[$i]][$s][$v]['ausleiheKlasse'] . "</div>" . 
							(($this->isAdmin || ($ausleihData[$datesAusleiheData[$i]][$s][$v]['ausleiheLehrer']) == $this->displayName)
									? 
										("<br /><font size=\"-1\"><a href=\"index.php?page=ausleihe&action=delete&objektID={$objekt['objektID']}&objektNummer=$v&ausleiheDatum={$datesAusleiheData[$i]}&ausleiheStunde=$s&firstDay={$_REQUEST['firstDay']}\"><i class=\"fa fa-trash\"></i> Löschen</a></font>")
									:
										("")
							)	.
							"</td>\n";
						}
						else {
							$html .= "<td align=\"center\" style=\"background-color: $bgColor; border: 1px solid black;border-bottom-width:2px;" . (($v == 1) ? ("border-left-width: 3px;") : ("border-left-style: dotted;")) . "vertical-align: middle;\" valign=\"middle\"><input type=\"checkbox\" name=\"{$datesAusleiheData[$i]}-{$s}-{$v}\" value=\"1\" style=\"transform: scale(1.5);\"></td>\n";
						}
					}
				}
			}
			
			if($bgColor == "#CECECE") $bgColor = "#FEFEFE";
			else $bgColor = "#CECECE";
		}
				
		$html .= "</table></td></tr></table>";


		return $html;
	}
	
	private function getDayName($i) {
		return (($i == 0) ? ("Montag") : (($i== 1) ? ("Dienstag") : (($i == 2) ? ("Mittwoch") : (($i == 3) ? ("Donnerstag") : (($i == 4) ? ("Freitag") : ("X"))))));
	}
	
	public static function hasSettings() {
		return true;
	}
	
	
	
	public static function getSiteDisplayName() {
		return 'Reservierung (Objekte)';
	}
	

	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "ausleihe-lehrer",
				'typ' => BOOLEAN,
				'titel' => "Zugriff auf das Ausleihmodul durch Lehrer?",
				'text' => ""
			],
			[
				'name' => "ausleihe-schueler",
				'typ' => BOOLEAN,
				'titel' => "Zugriff auf das Ausleihmodul durch Schüler?",
				'text' => ""
			],
			[
				'name' => "ausleihe-eltern",
				'typ' => BOOLEAN,
				'titel' => "Zugriff auf das Ausleihmodul durch Eltern?",
				'text' => ""
			]
		];
	}
	
	public static function getUserGroups() {
		return array();
	}
	
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Reservierung_Admin';
	}
	
	public static function displayAdministration($selfURL) {
		
		$group = usergroup::getGroupByName('Webportal_Ausleihe_Nicht_Schulperson');
		
		if($_REQUEST['action'] == 'addUser') {
			$group->addUser(intval($_REQUEST['userID']));
		}
		if($_REQUEST['action'] == 'removeUser') {
			$group->removeUser(intval($_REQUEST['userID']));
		}
		
		
		if($_REQUEST['add'] > 0) {
			DB::getDB()->query("INSERT INTO ausleihe_objekte (objektName, objektAnzahl, isActive, sortOrder, sumItems)
					values(
						'" . DB::getDB()->escapeString($_POST['objektName']) . "',
						'" . DB::getDB()->escapeString($_POST['objektAnzahl']) . "',
						1,
						'" . DB::getDB()->escapeString($_POST['objektSortOrder']) . "',
						'" . DB::getDB()->escapeString($_POST['sumItems']) . "')
					");
		}
		
		if($_REQUEST['delete'] > 0) {
			DB::getDB()->query("DELETE FROM ausleihe_objekte WHERE objektID='" . $_REQUEST['delete'] . "'");
			DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheObjektID='" . $_REQUEST['delete'] . "'");
		}
		
		if($_REQUEST['save'] > 0) {
			$objekte = DB::getDB()->query("SELECT * FROM ausleihe_objekte ORDER BY sortOrder ASC");
			
			$objektData = array();
			while($o = DB::getDB()->fetch_array($objekte)) {
				DB::getDB()->query("UPDATE ausleihe_objekte SET 
						objektName='" . DB::getDB()->escapeString($_POST['objektName_' . $o['objektID']]) . "',
						objektAnzahl='" . DB::getDB()->escapeString($_POST['objektAnzahl_' . $o['objektID']]) . "',
						sumItems='" . DB::getDB()->escapeString($_POST['sumItems_' . $o['objektID']]) . "',
						sortOrder='" . DB::getDB()->escapeString($_POST['sortOrder_' . $o['objektID']]) . "'
						WHERE objektID='" . $o['objektID'] . "'");
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}
		
		
		$ausleiheZugriff = administrationmodule::getUserListWithAddFunction($selfURL, 'ausleiheuser', 'addUser', 'removeUser', 'Benutzerzugriff auf das Ausleihemodul', 'Die hier angegebenen Benutzer haben Zugriff auf das Ausleihemodul zur Ausleihe der Objekte. (In den Einstellungen können dür die Gruppen Lehrer / Schüler und Eltern pauschale Freigaben erteilt werden.)', 'Webportal_Ausleihe_Nicht_Schulperson');
		
		
		$objekte = DB::getDB()->query("SELECT * FROM ausleihe_objekte ORDER BY sortOrder ASC");
		
		$objektData = array();
		while($o = DB::getDB()->fetch_array($objekte)) $objektData[] = $o;
		
		$objektHTML = "";
		for($i = 0; $i < sizeof($objektData); $i++) {
			$objektHTML .= "<tr>";
				$objektHTML .= "<td><input type=\"text\" name=\"objektName_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . $objektData[$i]['objektName'] . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"objektAnzahl_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['objektAnzahl']) . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sumItems_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sumItems']) . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sortOrder_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sortOrder']) . "\"></td>";
				$objektHTML .= "<td><a href=\"#\" onclick=\"javascript:if(confirm('Soll das Objekt wirklisch gelöscht werden?')) window.location.href='$selfURL&delete=" . $objektData[$i]['objektID'] . "';\"><i class=\"fa fa-trash\"></i> Löschen</a></td>";
				
				$objektHTML .= "</tr>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("ausleihe/admin") . "\";");
		
		return $html;
	}
	
	public static function getAdminMenuGroup() {
		return 'Kleinere Module';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file';
	}
	
	
	public static function getActionSchuljahreswechsel() {
		return 'Ausleihen aus dem alten Schuljahr löschen';
	}
	
	public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {

        DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheDatum < '" . $sqlDateFirstSchoolDay . "'");

    }
}


?>