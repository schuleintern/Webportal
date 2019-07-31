<?php

class NotenverwaltungZeugnisse extends AbstractPage {

	
	public function __construct() {
		
		if(!DB::getGlobalSettings()->hasNotenverwaltung) {
			die("Notenverwaltung nicht lizenziert.");
		}
		
		parent::__construct(['Notenverwaltung', 'Zeugnisse'],false,false,true);
		
		if(!DB::getSession()->isTeacher()) {
			new errorPage();
		}

	}

	public function execute() {
	    switch($_REQUEST['action']) {
	        
	    }
	}
	
	private function index() {
	    
	}

	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return [];
	}
	
	public static function getSiteDisplayName() {
		return 'Notenverwaltung - Startseite';
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

}


?>