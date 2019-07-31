<?php

class schulkalender extends AbstractKalenderPage {

	protected $title = "Schulkalender";
	protected $tableName = "kalender_schule";
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function checkKalenderAccess() {
		$this->checkLogin();
		// Allgemeiner Zugriff für Alle
		
		if(DB::getSession()->isAdmin()) $this->isAdmin = true;
		if(!$this->isAdmin) $this->isAdmin = DB::getSession()->isMember("Webportal_Schulkalender_Schreiben");
		
		
	}
	
	public function getTermineFromDatabase($begin = '', $end = '') {
		return Schultermin::getAll();
	}
	

	public static function hasSettings() {
		return false;
	}
	
	
	public static function getSettingsDescription() {
		return array();
	}
	
	public function sendICSFeedURL() {
	    $feed = ICSFeed::getExternerKalenderFeed($this->kalenderID, DB::getUserID());
	    
	    echo json_encode([
	        'feedURL' => $feed->getURL(),
	    ]);
	    
	    exit();
	    
	}
	
	
	
	public static function getSiteDisplayName(){
		return "Schulkalender";
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
		return 'fa fa-home';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Schulkalender_Schreiben';
	}
	
	public static function displayAdministration($selfURL) {
		$html = 'Die Moduladministratoren (und alle globalen Administratoren) haben auf alle Einträge im Schulkalender Zugriff und können Einträge anlegen und löschen.';
		
		$html .= "<br />Der Schulkalender ist immer für alle Benutzer sichtbar.";
		
		return $html;
	}
	
	public static function getActionSchuljahreswechsel() {
		return 'Einträge aus dem alten Schuljahr löschen';
	}
	
	public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
		
		DB::getDB()->query("DELETE FROM kalender_schule WHERE eintragDatumStart < '$sqlDateFirstSchoolDay'");
		
		
		
	}
}

