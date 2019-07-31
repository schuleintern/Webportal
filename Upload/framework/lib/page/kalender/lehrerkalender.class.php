<?php

class lehrerkalender extends AbstractKalenderPage {

	protected $title = "Lehrerkalender";
	protected $tableName = "kalender_lehrer";
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function checkKalenderAccess() {
		$this->checkLogin();
		
		if(!DB::getSession()->isMember("Webportal_Lehrerkalender_Sehen"))
			if(!DB::getSession()->isTeacher()) new errorPage("Nur für Lehrer verfügbar!");
		
		if(DB::getSession()->isAdmin()) $this->isAdmin = true;
		if(!$this->isAdmin) $this->isAdmin = DB::getSession()->isMember("Webportal_Lehrerkalender_Schreiben");
		
		
		if(DB::getSettings()->getValue('lehrerkalender_ical') != "") {
			$this->isAdmin = false;		// iCal kann man nicht schreiben.
		}
		
	}
	
	public function getTermineFromDatabase($begin = '', $end = '') {
		return Lehrertermin::getAll();
	}
	

	public static function hasSettings() {
		return true;
	}
	
	public function sendICSFeedURL() {
	    $feed = ICSFeed::getExternerKalenderFeed($this->kalenderID, DB::getUserID());
	    
	    echo json_encode([
	        'feedURL' => $feed->getURL(),
	    ]);
	    
	    exit();
	    
	}
	
	
	
	public static function getSettingsDescription() {
		return [
			'name' => 'lehrerkalender_ical',
			'typ' => 'ZEILE',
			'titel' => 'Lehrertermine aus einem iCal Feed anzeigen?',
			'text' => 'Wenn Sie die Termine im Lehrerkalender aus einem iCal Feed anzeigen lassen wollen, dann geben Sie bitte hier die URL zum iCal Feed an. ACHTUNG: Wenn Sie hier eine URL angeben, dann werden alle Termine, die bereits hier eingegeben wurden bei der nächsten Synchronisation gelöscht! Die Synchronisation erfolgt ca. alle 5 Minuten.'
		];
	}
		
	
	/*
	 * 	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 */
	
	
	public static function getSiteDisplayName(){
		return "Lehrerkalender";
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
		if($_REQUEST['action'] == "addKalenderAccess") {
			$group = usergroup::getGroupByName("Webportal_Lehrerkalender_Sehen");
			$group->addUser(intval($_POST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		 
		if($_REQUEST['action'] == "deleteKalenderAccess") {
			$group = usergroup::getGroupByName("Webportal_Lehrerkalender_Sehen");
			$group->removeUser(intval($_REQUEST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		 
		$html = 'Die Moduladministratoren (und alle globalen Administratoren) haben auf alle Einträge im Lehrerkalender Zugriff und können Einträge anlegen und löschen. (Nur wenn er nicht mit einem iCal Feed synchronisiert wird!)';
		
		$html .= '<br />Auf den Lehrerkalender haben nur Lehrer Zugriff.';
		 
		$box = administrationmodule::getUserListWithAddFunction($selfURL, "lehrerkalenderzugriff", "addKalenderAccess", "deleteKalenderAccess", "Benutzer mit Zugriff auf den Lehrerkalender","Lehrer haben immer Zugriff. Für einen Zugriff von nicht-Lehrern auf den kompletten Kalender hier bitte die Benutzer eintragen. (Gilt vor allem für Sekretariatskräfte.)", "Webportal_Lehrerkalender_Sehen");
		 
		$html = "<div class=\"row\"><div class=\"col-md-9\">$html</div><div class=\"col-md-3\">$box</div></div>";
		 
		return $html;
	}
	
	public static function getActionSchuljahreswechsel() {
		return 'Einträge aus dem alten Schuljahr löschen';
	}
	
	public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
		
		DB::getDB()->query("DELETE FROM kalender_lehrer WHERE eintragDatumStart < '$sqlDateFirstSchoolDay'");
		
		
		
	}
}

