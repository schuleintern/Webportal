<?php


class administrationusermatch extends AbstractPage {
	
	public function __construct() {
		parent::__construct(array("Administration", "Benutzeradministration"));
				
		new errorPage();
	}

	public function execute() {}
	
	private static $allGroups = [];
	private static $groupBeschreibungen = [];
	
	private static $showMessage = "";
	
	public static function displayAdministration($selfURL) {
		
		$pages = requesthandler::getAllowedActions();
		
		for($i = 0; $i < sizeof($pages); $i++)  {
				
			$site = $pages[$i];
			if($site != "error") {
				$classOK = false;
		
				if(class_exists($site)) {
					// OK :-)
					$classOK = !false;
				}
				else if(class_exists($site . "Page")) {
					$site = $site . "Page";
					$classOK = !false;
				}
		
				if($classOK) {
					$groups = $site::getUserGroups();
					for($g = 0; $g < sizeof($groups); $g++) {
						self::$allGroups[] = $groups[$g];
						self::$groupBeschreibungen[$groups[$g]['groupName']] = $groups[$g]['beschreibung'];
					}
				}
			}
		}
				
		return self::doIT();
	}
	
	private static function doIT() {
		
		switch($_GET['action']) {
			case "rematchusers":
				// Reset aller Zuordnungen
				DB::getDB()->query("UPDATE schueler SET schuelerUserID=0");
				DB::getDB()->query("UPDATE lehrer SET lehrerUserID=0");
				
				$matcher = new MatchUserFunctions();
				$messages = $matcher->matchLehrer();
				$messages .= $matcher->matchSchueler();
				
				$html = "";
				
				eval("\$html = \"" . DB::getTPL()->get("administration/usermatch/rematchdone") . "\";");
				return $html;
				
			break;
			
			default:
				return self::showIndex();
			break;
		}
	}
	
	private function showIndex() {
		
		if($_GET['showNetwork'] > 0) {
			$tabs = "";
			
			$network = DB::getDB()->escapeString($_GET['network']);
			
			$networks = DB::getDB()->query("SELECT DISTINCT userNetwork FROM users WHERE userNetwork != 'SCHULEINTERN_ELTERN' ORDER BY userNetwork ASC");
			
			$first = true;
			while($net = DB::getDB()->fetch_array($networks)) {
				if($first) {
					if($network == "") $network = $net['userNetwork'];
					
					$first = false;
				}
				$tabs .= "<li" . (($net['userNetwork'] == $network) ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationmodule&module=administrationusermatch&showNetwork=1&network=" . $net['userNetwork'] . "\"><i class=\"fa fa-users\"></i> " . $net['userNetwork'] . "</a></li>\r\n";
			}
			
			$tabs .= "<li><a href=\"index.php?page=administrationmodule&module=administrationusermatch&network=Schueler\"><i class=\"fa fa-users\"></i> ASV Schüler</a></li>\r\n";
			$tabs .= "<li><a href=\"index.php?page=administrationmodule&module=administrationusermatch&network=Lehrer\"><i class=\"fa fa-users\"></i> ASV Lehrer</a></li>\r\n";
			
			$users = DB::getDB()->query("SELECT * FROM users  LEFT JOIN lehrer ON userID=lehrerUserID LEFT JOIN schueler ON userID=schuelerUserID WHERE userNetwork = '" . $network . "' ORDER BY userName");

			$userHTML = "";
			while($user = DB::getDB()->fetch_array($users)) {
				$typ = "Unbekannt";
				$zuordnung = "<font color=\"red\">Keine</font>";
				
				
				if($user['schuelerAsvID'] != "") {
					$typ = "Schüler";
					$zuordnung = "ASV: " . $user['schuelerName'] . ", " . $user['schuelerRufname'];
					$schueler = new schueler($user);
					if($schueler->isAusgetreten()) $zuordnung .= " (Ausgetreten)";
				}
				if($user['lehrerAsvID'] != "") {
					$typ = "Lehrer";
					$zuordnung = "ASV: " . $user['lehrerName'] . ", " . $user['lehrerRufname'];
				}
				
				$name = $user['userName'] . " (" . $user['userLastName'] . ", " . $user['userFirstName'] . ")" ;
				
				
				eval("\$userHTML .= \"" . DB::getTPL()->get("administration/usermatch/user_bit") . "\";");
			}
		
		}
		else {
			$tabs = "";
				
			$network = DB::getDB()->escapeString($_GET['network']);
				
			$networks = DB::getDB()->query("SELECT DISTINCT userNetwork FROM users WHERE userNetwork != 'SCHULEINTERN_ELTERN' ORDER BY userNetwork ASC");
				
			$first = true;
			while($net = DB::getDB()->fetch_array($networks)) {
				if($first) {
					if($network == "") $network = $net['userNetwork'];
						
					$first = false;
				}
				$tabs .= "<li" . (($net['userNetwork'] == $network) ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationmodule&module=administrationusermatch&showNetwork=1&network=" . $net['userNetwork'] . "\"><i class=\"fa fa-users\"></i> " . $net['userNetwork'] . "</a></li>\r\n";
			}
				
			$tabs .= "<li" . (("Schueler" == $network) ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationmodule&module=administrationusermatch&network=Schueler\"><i class=\"fa fa-users\"></i> ASV Schüler</a></li>\r\n";
			$tabs .= "<li" . (("Lehrer" == $network) ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationmodule&module=administrationusermatch&network=Lehrer\"><i class=\"fa fa-users\"></i> ASV Lehrer</a></li>\r\n";
			
			if($network == "Schueler") {
				$users = DB::getDB()->query("SELECT * FROM schueler LEFT JOIN users ON userID=schuelerUserID ORDER BY userName");
			}
			else {
				$users = DB::getDB()->query("SELECT * FROM lehrer LEFT JOIN users ON userID=lehrerUserID ORDER BY userName");
			}
			
			$userHTML = "";
			while($user = DB::getDB()->fetch_array($users)) {
				$typ = "Unbekannt";
				$zuordnung = "<font color=\"red\">Keine</font>";
			
			
				if($user['userID'] > 0) {
					$typ = "Benutzer";
					$zuordnung = $user['userName'] . " (" . $user['userNetwork'] . ")";
				}
			
				if($network == "Schueler") {
					$name = $user['schuelerName'] . ", " . $user['schuelerRufname'];
					$s = new schueler($user);
					if($s->isAusgetreten()) $name .= " <i>Ausgetreten zum " . DateFunctions::getNaturalDateFromMySQLDate($s->getAustrittDatumAsMySQLDate()) . "</i>";
				}
				
				
				
				else $name = $user['lehrerName'] . ", " . $user['lehrerRufname'] . " (" . $user['lehrerKuerzel'] . ")";
			
				eval("\$userHTML .= \"" . DB::getTPL()->get("administration/usermatch/user_bit") . "\";");
			}
		}
		
		if(self::$showMessage != "") {
			self::$showMessage = "<div class=\"callout callout-info\">" . self::$showMessage . "</div>";
		}
		
		$html = "";
		eval("\$html = \"" . DB::getTPL()->get("administration/usermatch/index") . "\";");
		
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
		return 'Benutzerzuordnung';
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
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuGroup() {
		return 'Benutzerverwaltung';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fas fa-people';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-arrows-alt-h';
	}

}


?>