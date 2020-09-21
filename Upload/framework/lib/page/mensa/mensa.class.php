<?php


class mensa extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Mensa"));
				
		$this->checkLogin();
		
		
	}

	public function execute() {
		
		
		eval("echo(\"" . DB::getTPL()->get("mensa/index"). "\");");
		
	}
	
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Mensa';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return false;
		//return 'Webportal_Klassenlisten_Admin';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fas fa-utensils';
	}
	
	public static function getAdminMenuIcon() {
		return 'fas fa-utensils';
	}
	

	public static function displayAdministration($selfURL) {
		 
	}
}


?>