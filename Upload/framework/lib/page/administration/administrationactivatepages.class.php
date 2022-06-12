<?php

class administrationactivatepages extends AbstractPage {

	private $info;
	
	public function __construct() {
		$this->needLicense = false;
		
		parent::__construct(array("Administration", "Modulstatus"), false, true);
		
		$this->checkLogin();
		
		if(!DB::getSession()->isAdmin()) {
			header("Location: index.php");
			exit(0);
		}
		
	}

	public function execute() {
		$allPages = requesthandler::getAllowedActions();
		
		$pageStatus = "";
		
		sort($allPages);
		
		for($i = 0; $i < sizeof($allPages); $i++) {
			
			/**
			 * 
			 * @var AbstractPage $pageName
			 */
			$pageName = $allPages[$i];
			
			$skip = false;
			
			$hinweis = "";
						
			if(sizeof($pageName::onlyForSchool()) > 0) {
				// Nur für bestimtme Schulen
				if(!in_array(DB::getGlobalSettings()->schulnummer,$pageName::onlyForSchool())) {
					$skip = true;
				}
				else {
					$hinweis = "<br /><i>Diese Funktion ist speziell für Schule " . DB::getGlobalSettings()->schulnummer . " freigeschaltet.";
				}
			}
			
			if($pageName::siteIsAlwaysActive()) {
				$skip = true;
			}
			
			if(!$skip) {
				
				if(sizeof($pageName::dependsPage()) > 0 ) {
					$hinweis = "<br /><i>Diese Seite ist abhängig von:</i><br />";
					for($s = 0; $s < sizeof($pageName::dependsPage()); $s++) {
						$hinweis .= (($s > 0) ? ", " : "") . $pageName::dependsPage()[$s]::getSiteDisplayName();
					}
				}
				
				
				$displayName = "<b>" . $pageName::getSiteDisplayName() . "</b>";


                if($displayName == "") $displayName = "<font color=\"red\">" . $allPages[$i] . "</font>";

                if($pageName::isBeta()) $displayName .= ' <span class="label bg-red pull-right"><i class="fa fa-info"></i> Modul im Beta Test</span>';

				if(AbstractPage::isActive($allPages[$i])) {
                    $pageStatus .= "<tr><td>" . $displayName . $hinweis . "</td><td>";

                    if($_GET['deActivatePage'] == $allPages[$i] && DB::checkDemoAccess()) {
						
						// Seiten suchen, die von dieser Seite abhängig sind.
						$dependsFromMe = [];
						
						for($u = 0; $u < sizeof($allPages); $u++) {
							for($o = 0; $o < sizeof($allPages[$u]::dependsPage()); $o++) {
								if($allPages[$u]::dependsPage()[$o] == $allPages[$i]) {
									$dependsFromMe[] = $allPages[$u];
								}
							}
						}
						
						if(sizeof($dependsFromMe) > 0 ) {
							for($s = 0; $s < sizeof($dependsFromMe); $s++) {
								DB::getDB()->query("INSERT INTO site_activation (siteName, siteIsActive) values('" . $dependsFromMe[$s] . "',0) ON DUPLICATE KEY UPDATE siteIsActive=0");
							}
						}
						
						DB::getDB()->query("INSERT INTO site_activation (siteName, siteIsActive) values('" . $allPages[$i] . "',0) ON DUPLICATE KEY UPDATE siteIsActive=0");
						header("Location: index.php?page=administrationactivatepages");
						exit();
					}
					$pageStatus .= "<a href=\"index.php?page=administrationactivatepages&deActivatePage=" . $allPages[$i] . "\" class='btn btn-danger btn-sm btn-block'><i class=\"fa fa-toggle-off\"></i> Seite deaktivieren</a>";
				}
				else {
                    $pageStatus .= "<tr><td>" . $displayName . " <label class='label label-danger'>Seite nicht aktiv</label>" . $hinweis . "</td><td>";


                    if($_GET['activatePage'] == $allPages[$i] && DB::checkDemoAccess()) {
						
						if(sizeof($pageName::dependsPage()) > 0 ) {
							for($s = 0; $s < sizeof($pageName::dependsPage()); $s++) {
								DB::getDB()->query("INSERT INTO site_activation (siteName, siteIsActive) values('" . $pageName::dependsPage()[$s] . "',1) ON DUPLICATE KEY UPDATE siteIsActive=1");
							}
						}
						
						DB::getDB()->query("INSERT INTO site_activation (siteName, siteIsActive) values('" . $allPages[$i] . "',1) ON DUPLICATE KEY UPDATE siteIsActive=1");
						header("Location: index.php?page=administrationactivatepages");
						exit();
					}
					$pageStatus .= "<a href=\"index.php?page=administrationactivatepages&activatePage=" . $allPages[$i] . "\" class=\"btn btn-success btn-sm btn-block\"><i class=\"fa fa-toggle-on\"></i> Seite aktivieren</a>";
						
				}
			}
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("administration/activatefunctions/index") . "\");");
	}
	
	
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Modulstatus';
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
	
	public static function need2Factor() {
	    return TwoFactor::force2FAForAdmin();
	}

    public static function getAdminMenuGroup() {
        return 'System';
    }
    public static function getAdminMenuIcon() {
        return 'fa fas fa-toggle-on';
    }
    public static function hasAdmin() {
        return false;
    }
}


?>