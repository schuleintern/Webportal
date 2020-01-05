<?php

class schulbuecher extends AbstractPage {
	
	public function __construct() {
		parent::__construct([],true);
		
		$this->checkLogin();

	}

	public function execute() {
			
	}
	
	
	public static function getNotifyItems() {
		return array();
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
		return 'Schulbücher';
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
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-book';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulbücher';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-book';
	}
	
	public static function displayAdministration($selfURL) {
		$return = 'Jeder Administator dieses Moduls ist Verwalter der Schulbuchbücherei.';
		
		return $return;
	}	
	
	public static function getAdminGroup() {
		return 'Webportal_Schulbuch_Admin';
	}
}


?>