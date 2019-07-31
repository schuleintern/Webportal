<?php

class smsadmin extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
		
	}
	
	private function index() {
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "sms-schueler",
				'typ' => BOOLEAN,
				'titel' => "Handynummern der Schüler hinterlegbar machen?",
				'text' => ""
			],
			[
				'name' => "sms-eltern",
				'typ' => BOOLEAN,
				'titel' => "Handynummern der Eltern hinterlegbar machen?",
				'text' => ""
			],
			[
				'name' => "sms-lehrer",
				'typ' => BOOLEAN,
				'titel' => "Handynummern der Lehrer hinterlegbar machen?",
				'text' => ""
			],
		];
	}
	
	
	public static function getSiteDisplayName() {
		return 'SMS Versand';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_SMS_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-mobile';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-mobile';
	}
	
	public static function getAdminMenuGroup() {
		return 'SMS Versand';
	}

	public static function siteIsAlwaysActive() {
		return true;
	}
	/**
	 * Überprüft, ob die Seite eine Administration hat.
	 * @return boolean
	 */
	public static function hasAdmin() {
		return false;
	}

	public static function displayAdministration($selfURL) {
		
		if($_GET['action'] == 'activeSMS' && $_POST['ok'] > 0) {
			DB::getSettings()->setValue("sms-isActive", 1);
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'deactiveSMS') {
			DB::getSettings()->setValue("sms-isActive", 0);
			header("Location: $selfURL");
			exit(0);
		}
		
		$smsStatus = sms::isSMSActive();
		
		$html = "";
		eval("\$html = \"" . DB::getTPL()->get("sms/admin/index") . "\";");
		
		return $html;
	}
		
}


?>