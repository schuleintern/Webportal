<?php

class Backup extends AbstractPage {
	
	public function __construct() {
        // Seite ist immer sichtbar.
	}

	public function execute() {
		//MathCaptcha::showCaptcha();
        echo '###backup###';
        exit(0);
	}
	
	public static function hasSettings() {
		return false;
	}
    
    
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Backup';
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
	public static function getAdminMenuGroup() {
		return 'E-Mailverwaltung';
	}
}


?>