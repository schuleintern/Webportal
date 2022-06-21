<?php



class adminsupport extends AbstractPage {


	public function __construct() {
		parent::__construct(array(""));

		new errorPage();

	}

	public function execute() {
	    // No execution
	}

	public static function getNotifyItems() {
		return array();
	}

	public static function hasSettings() {
	    return false;
	}

	
	public static function getSiteDisplayName() {
		return 'Admin - Support';
	}


	public static function hasAdmin() {
		return true;
	}
	
	public static function displayAdministration() {
	    $html = "";
	    eval("\$html = \"" . DB::getTPL()->get("administration/support/adminsupport") . "\";");
	    return $html;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-life-ring';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Administration_Adminsupport';
	}
	
	public static function getAdminMenuGroup() {
		return 'System';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-life-ring';
	}
	
	public static function siteIsAlwaysActive() {
	    return true;
	}
}


?>