<?php

class NotenverwaltungIndex extends AbstractPage {

	
	public function __construct() {
		
		if(!DB::getGlobalSettings()->hasNotenverwaltung) {
			die("Notenverwaltung nicht aktiv.");
		}

		
		parent::__construct(['Notenverwaltung'],false,false,true);
		
		$this->checkLogin();
		
		if(!DB::getSession()->isTeacher()) {
			new errorPage();
		}

	}

	public function execute() {
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get('notenverwaltung/index/index') . "\");");
	}

	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return [];
	}
	
	public static function getSiteDisplayName() {
		return 'Allgemeines';
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Notenverwaltung_Admin';
	}
	
	public static function need2Factor() {
	    return TwoFactor::is2FAActive() && TwoFactor::force2FAForNoten();
	}
	
	public static function getAdminMenuGroup() {
	    return "Notenverwaltung";
	}
	
	public static function getAdminMenuGroupIcon() {
	    return "fa fas fa-award";
	}
	
	public static function getAdminMenuIcon() {
	    return "fa fa-cogs";
	}
	
	public static function hasAdmin() {
	    return true;
	}

	public static function displayAdministration($selfURL) {
	    return "";
	}
	
}


?>