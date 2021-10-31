<?php

class AdminMenu extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
			new errorPage();
	}

	public static function siteIsAlwaysActive() {
		return true;
	}

	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return array();
	}
	
	public static function getSiteDisplayName() {
		return 'Menu';
	}
    public static function getAdminMenuIcon() {
        return 'fa fas fa-ellipsis-v';
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fas fa-bars';
    }

    public static function getAdminMenuGroup() {
        return 'Navigation';
    }

    public static function hasAdmin() {
        return true;
    }

	public static function displayAdministration($selfURL) {

		$html = 'HTML';


		eval("\$html = \"" . DB::getTPL()->get("administration/menu/index") . "\";");

		return $html;
	}

}


?>