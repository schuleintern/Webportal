<?php

class administration extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		$this->needLicense = false;
		$this->sitename = "Administration / Allgemeines";
		
		parent::__construct(array("Administration", "Administration"), false, true);
		
		$this->checkLogin();

		if(!DB::getSession()->isAnyAdmin()) {
			// Nur für Admins
			header("Location: index.php");
			exit(0);
		}

	}

	public function execute() {


        include_once PATH_LIB.'menu'.DS.'admin.class.php';
        $adminMenu = new SystemAdminMenu();

        $html = '';
        foreach ($adminMenu->data as $item) {
            $html .= '<h4>'.$item['title'].'</h4>';
            $html .= '<ul>'.$item['html'].'</ul>';
        }

        $countExtUpdate = 0;
        $extensionsServer = DB::getGlobalSettings()->extensionsServer;
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $extStore = json_decode(file_get_contents($extensionsServer."extensions.json?task=checkVersion", false, stream_context_create($arrContextOptions)));
        $result = DB::getDB()->query('SELECT `name`,`active`,`uniqid`,`version`,`folder` FROM `extensions` ');
        while($row = DB::getDB()->fetch_array($result)) {
            if (AdminExtensions::checkUpdate($extStore, $row)) {
                $countExtUpdate++;
            }
        }


        $updateSys = false;
        $releaseID = DB::getSettings()->getValue("current-release-id");
        $updateserver = DB::getGlobalSettings()->updateServer;
        $schulnummern = implode('-',DB::getSchulnummern());
        $versionInfo = json_decode(file_get_contents($updateserver . "/api/release/" . $releaseID . "/" . $schulnummern), true);
        if ($versionInfo['id'] > 0) {
            if ($versionInfo['nextVersionID'] > 0) {
                $updateSys = true;
            }
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