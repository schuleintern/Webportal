<?php

class administration extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		$this->needLicense = false;
		$this->sitename = "Administration / Allgemeines";
		
		parent::__construct(array("Administration", "Allgemeines, Lizenz"), false, true);
		
		$this->checkLogin();

		if(!DB::getSession()->isAnyAdmin()) {
			// Nur für Admins
			header("Location: index.php");
			exit(0);
		}
		
	}

	public function execute() {
		$users = DB::getDB()->query("SELECT * FROM sessions JOIN users ON sessions.sessionUserID=users.userID WHERE sessionLastActivity > (UNIX_TIMESTAMP()-600)");
		
		$userList = "";
		$count = 0;
		while($u = DB::getDB()->fetch_array($users)) {
			$count++;
			$userList .= "<li>" . $u['userName'] . " (" . $u['userLastName'] . ", " . $u['userFirstName'] . ")";
		}
		
		eval("echo(\"" . DB::getTPL()->get("administration/index") . "\");");
	}
	
	public static function hasSettings() {
		return false;
	}
	
	
	public static function getSiteDisplayName() {
		return 'Adminoptionen';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];	
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
	
	/**
	 * Überprüft, ob die Seite eine Benutzeradministration hat.
	 * @return boolean
	 */
	public static function hasUserAdmin() {
		return false;
	}
	
	/**
	 * Überprüft, ob der Nutzer mit den angegeben Benutzergruppen Zugriff auf die Administration hat.
	 * @param String[] $userGroups Benutzergruppen
	 * @return boolean
	 */
	public static function userHasAdminAccess($userGroups) {
		return false;
	}
	
	/**
	 * Zeigt die Administration an. (Nur Bereich innerhalb von einem TabbedPane, keinen Footer etc.)
	 * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
	 */
	public static function displayAdministration($selfURL) {
		return;
	}
	
	/**
	 * Zeigt die Benutzeradministration an. (Nur Bereich innerhalb von einem TabbedPane, keinen Footer etc.)
	 * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
	 */
	public static function displayUserAdministration($selfURL) {
		return;
	}
	
	public static function getAllModulesWithAnyAdministration() {
		$all = [];
		
		$allModules = requesthandler::getAllowedActions();
		
		for($i = 0; $i < sizeof($allModules); $i++) {
			if($allModules[$i]::hasAdmin() || $allModules[$i]::hasUserAdmin()) {
				$all[] = $allModules[$i];
			}
		}
		
		return $all;
	}
	
	
	public static function need2Factor() {
	    return TwoFactor::force2FAForAdmin();
	}
	
	
}


?>