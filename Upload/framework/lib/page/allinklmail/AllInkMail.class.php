<?php


class AllInkMail extends AbstractPage {
	

	public function __construct() {		
		parent::__construct(array("E-Mail Account"));
		
		$this->checkLogin(); 
		
		if(!DB::getSession()->isTeacher()) new errorPage();       // Nur für Lehrer
	}

	public function execute() {

	    // Zeige Mail Adresse
	    $me = DB::getSession()->getUser();
	    
	    if($me->getAllInklMail() != null) {
	        $mail = $me->getAllInklMail();
	        $password = $me->getAllInklMailPassword();
	        
	        
	        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("allinklmail/index") . "\");");
	        
	    }
	    else {
	        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("allinklmail/noaccount") . "\");");
	    }
	    
	    
	}

	public static function hasSettings() {
		return true;
	}

	public static function getSiteDisplayName() {
		return 'AllInkl Mail Accounts';
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [
		    [
		        'name' => 'all-inkl-mail-trenner',
		        'typ' => 'TRENNER',
		        'titel' => 'Bitte beachten: Die Funktion steht nur zur Verfügung, wenn KAS Zugangsdaten in der "mainsettings.php" eingetragen sind!',
		        'text' => "Aktueller Status: " . DB::getGlobalSettings()->kasAccount['Username'] != "" ? "<font color=\"green\">Zugangsdaten vorhanden.</font>" : "<font color=\"red\">Zugangsdaten nicht vorhanden!</font>"
		        
		    ],
			[
				'name' => "all-inkl-mail-active",
				'typ' => 'BOOLEAN',
				'titel' => "Mail Accounts für Lehrer erstellen?",
				'text' => ""
			],
		    [
		        'name' => "all-inkl-mail-format",
		        'typ' => 'SELECT',
		        'titel' => "Mailformat",
		        'text' => "ACHTUNG: Beim Wechsel des Schemas werden nur zukünftige Mailadressen angepasst!",
		        'options' => [
		            [
		                'value' => 'vorname.nachname',
		                'name' => 'Vorname.Nachname@' . DB::getGlobalSettings()->kasMailDomain
		            ],
		            [
		                'value' => 'v.nachname',
		                'name' => 'V.Nachname@' . DB::getGlobalSettings()->kasMailDomain
		            ],
		            [
		                'value' => 'k',
		                'name' => 'Kürzel@' . DB::getGlobalSettings()->kasMailDomain
		            ]
		        ]
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
		return 'Webportal_AllInklMailAdmin';
	}
	
	public static function displayAdministration($selfURL) {
	    
	}
	
	public static function getAdminMenuGroup() {
		return 'Kleinere Module';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file';
	}

}


?>