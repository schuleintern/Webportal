<?php

/**
 * Externe Kalender
 *
 * @author Christian Spitschka
 *
 */
class extKalender extends AbstractKalenderPage {
	
	protected $title = "Lehrerkalender";
	protected $tableName = "kalender_lehrer";
	
	private $kalender = [];
	
	public function __construct() {
		
		$kalender = DB::getDB()->query_first("SELECT * FROM externe_kalender WHERE kalenderID='" .intval($_REQUEST['kalenderID']) . "'");
		
		if($kalender['kalenderID'] > 0) {
			$this->kalender = $kalender;
			
			$this->title = $this->kalender['kalenderName'];
			
			$this->isExternalKalender = true;
			
			parent::__construct();
			
		}
		else {
			new errorPage('kalender nicht vorhanden');
		}
	}
	
	public function sendICSFeedURL() {
	        $feed = ICSFeed::getExternerKalenderFeed($this->kalenderID, DB::getUserID());
	        
	        echo json_encode([
	            'feedURL' => $feed->getURL(),
	        ]);
	        
	        exit();
	        
	}
	
	public static function getKalenderWithAccess() {
	    $kalenderExtern = DB::getDB()->query("SELECT * FROM externe_kalender");
	    
	    $calData = [];
	    
	    while($kalender = DB::getDB()->fetch_array($kalenderExtern)) {
	        $access = false;
	        if(DB::getSession()->isAdmin()) $access = true;
	        
	        if(DB::getSession()->isMember('Webportal_Externe_Kalender_Lesen_' . $kalender['kalenderID'])) $access = true;
	        
	        if(DB::getSession()->isPupil() && $kalender['kalenderAccessSchueler'] == 1) $access = true;
	        if(DB::getSession()->isTeacher() && $kalender['kalenderAccessLehrer'] == 1) $access = true;
	        if(DB::getSession()->isEltern() && $kalender['kalenderAccessEltern'] == 1) $access = true;
	        
	        if($access) {
	            $calData[] = $kalender;
	        }
	    }
	    
	    return $calData;
	}
	
	
	public function checkKalenderAccess() {
		$this->checkLogin();
		
		$this->isAdmin = false;		// iCal kann man nicht schreiben.
		
		// if($this->kalender['kalenderIcalFeed'] != '') $this->isAdmin = true;
		
		
		if(DB::getSession()->isAdmin()) return;
		
		if(DB::getSession()->isMember('Webportal_Externe_Kalender_Lesen_' . $this->kalender['kalenderID'])) return;
		
		if(DB::getSession()->isPupil() && $this->kalender['kalenderAccessSchueler'] == 1) return;
		if(DB::getSession()->isTeacher() && $this->kalender['kalenderAccessLehrer'] == 1) return;
		if(DB::getSession()->isEltern() && $this->kalender['kalenderAccessEltern'] == 1) return;
		
		new errorPage('Kein Zugriff');	
	}
	
	public function getTermineFromDatabase($begin = '', $end = '') {
		return ExtKalenderTermin::getAll($this->kalender['kalenderID'], $begin, $end);
	}
	
	
	public static function hasSettings() {
		return false;
	}
	
	
	public static function getSettingsDescription() {
		return [];
	}
	
	
	/*
	 * 	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 */
	
	
	public static function getSiteDisplayName(){
		return "Externe Kalender (iCAL/Office365)";
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuGroup() {
		return 'Kalender';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-calendar';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-male';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Lehrerkalender_Schreiben';
	}
	
	public static function displayAdministration($selfURL) {
		
		if($_REQUEST['action'] == 'addKalender') {
			DB::getDB()->query("INSERT INTO externe_kalender (kalenderName, kalenderAccessSchueler, kalenderAccessLehrer, kalenderAccessEltern, kalenderIcalFeed,office365Username) values(
				'" . DB::getDB()->escapeString($_POST['kalenderName']) . "',
				'" . DB::getDB()->escapeString($_POST['kalenderZugriffSchueler']) . "',
				'" . DB::getDB()->escapeString($_POST['kalenderZugriffLehrer']) . "',
				'" . DB::getDB()->escapeString($_POST['kalenderZugriffEltern']) . "',
				'" . DB::getDB()->escapeString($_POST['kalenderIcalFeed']) . "',
                '" . DB::getDB()->escapeString($_POST['kalenderOffice365Username']) . "'
			)");
			
			header("Location: $selfURL");
			exit();
		}
		
		if($_REQUEST['action'] == 'deleteKalender') {
			DB::getDB()->query("DELETE FROM externe_kalender WHERE kalenderID='" . intval($_REQUEST['kalenderID']) . "'");
			header("Location: $selfURL");
			exit();
		}
		
		if($_REQUEST['action'] == 'editKalender') {
			DB::getDB()->query("UPDATE externe_kalender SET
				kalenderName = '" . DB::getDB()->escapeString($_POST['kalenderName']) . "',
				kalenderAccessSchueler = '" . DB::getDB()->escapeString($_POST['kalenderZugriffSchueler']) . "',
				kalenderAccessLehrer = '" . DB::getDB()->escapeString($_POST['kalenderZugriffLehrer']) . "',
				kalenderAccessEltern = '" . DB::getDB()->escapeString($_POST['kalenderZugriffEltern']) . "',
				kalenderIcalFeed = '" . DB::getDB()->escapeString($_POST['kalenderIcalFeed']) . "',
                office365Username = '" . DB::getDB()->escapeString($_POST['kalenderOffice365Username']) . "'


					
			WHERE kalenderID='" . intval($_REQUEST['kalenderID']) . "'");
			header("Location: $selfURL");
			exit();
		}
		
		if($_REQUEST['action'] == "addKalenderAccess") {
			$group = usergroup::getGroupByName("Webportal_Externe_Kalender_Lesen_" . intval($_GET['kalenderID']));
			$group->addUser(intval($_POST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_REQUEST['action'] == "deleteKalenderAccess") {
			$group = usergroup::getGroupByName("Webportal_Externe_Kalender_Lesen_" . intval($_GET['kalenderID']));
			$group->removeUser(intval($_REQUEST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		
		$kalenderSQL = DB::getDB()->query("SELECT * FROM externe_kalender");
		
		$kalenderHTML = '';
		
		while($kalender = DB::getDB()->fetch_array($kalenderSQL)) {
			$checkedLehrer = (($kalender['kalenderAccessLehrer'] > 0) ? (" checked=\"checked\"") : (""));
			$checkedSchueler = (($kalender['kalenderAccessSchueler'] > 0) ? (" checked=\"checked\"") : (""));
			$checkedEltern = (($kalender['kalenderAccessEltern'] > 0) ? (" checked=\"checked\"") : (""));
			
			$userBox = administrationmodule::getUserListWithAddFunction($selfURL . "&kalenderID=" . $kalender['kalenderID'], "kalenderzugriff" . $kalender['kalenderID'], "addKalenderAccess", "deleteKalenderAccess", "Benutzer mit Zugriff auf den Kalender {$kalender['kalenderName']}","Die Gruppen Schüler, Lehrer und Eltern können links freigegeben werden. Andere Benutzer (z.B. Sekretariat) können wir hinzugefügt werden.", "Webportal_Externe_Kalender_Lesen_" . $kalender['kalenderID']);
						
			eval("\$kalenderHTML .= \"" . DB::getTPL()->get("externeKalender/admin/bit") . "\";");
		}
			
		
		
		$html = '';
		
		eval("\$html = \"" . DB::getTPL()->get("externeKalender/admin/index") . "\";");
		
		return $html;
		
	}
}

