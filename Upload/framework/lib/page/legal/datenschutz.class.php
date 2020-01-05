<?php




class datenschutz extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Datenschutz"));
	}

	public function execute() {
		$dstext = DB::getSettings()->getValue("datenschutz-erklaerung");
		
		$status = "";
		
		$needToConfirm = false;
		
		if(DB::isLoggedIn()) {
			if(self::needFreigabe(DB::getSession()->getUser())) {
				if(self::isFreigegeben(DB::getSession()->getUser())) {
					$status = "<div class=\"callout callout-success\"><i class=\"fa fa-check\"></i> Sie haben der Datenschutzerklärung zugestimmt.</div>";
				}
				else {
					$needToConfirm = true;
					$status = "<div class=\"callout callout-danger\"><i class=\"fa fa-ban\"></i> Sie haben der Datenschutzerklärung noch nicht zugestimmt</div>";
				}
			}
		}
		
		if($_REQUEST['action'] == 'confirm' && $needToConfirm) {
			if($_POST['confirm'] == "1") {
				DB::getDB()->query_first("INSERT INTO datenschutz_erklaerung (userID,userConfirmed) values('" . DB::getSession()->getUserID() . "', UNIX_TIMESTAMP())");
				header("Location: index.php?page=datenschutz");
				exit(0);
			}
		}
		
		
		$confirmField = "";
		if($needToConfirm) {
			eval("\$confirmField = \"" . DB::getTPL()->get("datenschutz/confirm") . "\";");
		}
		
		$dsbeauftragter = "";
		
		$group = usergroup::getGroupByName('Webportal_Datenschutzbeauftragte');
		
		$users = $group->getMembers();
		
		for($i = 0; $i < sizeof($users); $i++) {
		    $dsbeauftragter .= $users[$i]->getDisplayName() . "<br />";
		}
		
		$dsbeauftragter .= "Kontakt: " . DB::getSettings()->getValue("dsbKontakt");
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("datenschutz/index") . "\");");
		exit(0);
	}
	
	/**
	 * 
	 * @param user $user
	 */
	public static function needFreigabe($user) {
		// if($user->isAdmin()) return true;
		if($user->isTeacher()) return false;
		if($user->isPupil()) return false;
		if($user->isEltern() && DB::getSettings()->getBoolean("datenschutz-eltern-need-zustimmung")) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Überprüft, ob eine Freigabe vorliegt.
	 * @param user $user
	 * @return int|NULL
	 */
	public static function isFreigegeben($user) {
		$data = DB::getDB()->query_first("SELECT * FROM datenschutz_erklaerung  WHERE userID='" . $user->getUserID() . "'");
	
		if($data['userConfirmed'] > 0) {
			return $data['userConfirmed'];
		}
		else return null;
	
	}

	public static function getSettingsDescription() {
		return [];
	}
	
	public static function getSiteDisplayName() {
		return "Datenschutz";
	}
	
	public static function hasSettings() {
		return false;
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
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return "Webportal_Datenschutz";
	}

	public static function displayAdministration($selfURL) {
		
		switch($_REQUEST['action']) {
			case 'saveDSFreigabe':
				$newText = $_POST['dsErklaerung'];
				if($newText != DB::getSettings()->getValue("datenschutz-erklaerung")) {
					// Speichern
					DB::getSettings()->setValue("datenschutz-erklaerung", $newText);
					
					if($_POST['osc'] > 0) {
						// NIX
					}
					else {
						DB::getDB()->query("DELETE FROM datenschutz_erklaerung");
					}
					
					new infoPage("Der Text der Datenschutzerklärung wurde abgespeichert."
						 . (($_POST['osc'] > 0)	 ? "Die Benutzer müssen <u>nicht</u> erneut zustimmen." : "Alle Zustimmungen wurden gelöscht. Die Benutzer müssen der Datenschutzerklärung wieder zustimmen.")
							
					,$selfURL);
				}
				else {
					// Ignore
					new infoPage("Keine Änderungen vorgenommen.",$selfURL);
					exit(0);
				}
			break;
			
			case 'saveDSSettings':
				DB::getSettings()->setValue("datenschutz-kein-eintragzeitpunkt", $_POST['noEintragzeitpunkt'] > 0);
				DB::getSettings()->setValue("datenschutz-eltern-need-zustimmung", $_POST["elternNeedZustimmung"] > 0);
				DB::getSettings()->setValue('dsbKontakt', $_REQUEST['dsbKontakt']);
				new infoPage("Änderungen gespeichert.",$selfURL);
				exit(0);
		
		}
		
		$text = DB::getSettings()->getValue("datenschutz-erklaerung");
		
		$selectedZeitpunkt = ((DB::getSettings()->getBoolean("datenschutz-kein-eintragzeitpunkt")) ? " checked=\"checked\"" : "");
		$selectedEltern = ((DB::getSettings()->getBoolean("datenschutz-eltern-need-zustimmung")) ? " checked=\"checked\"" : "");
		
		
		$usergroup = usergroup::getGroupByName('Webportal_Datenschutzbeauftragte');
		
		if($_REQUEST['action'] == 'addUser') {
		    $usergroup->addUser($_POST['userID']);
		    header("Location: $selfURL&userAdded=1");
		    exit(0);
		    
		}
		
		if($_REQUEST['action'] == 'removeUser') {
		    $usergroup->removeUser($_REQUEST['userID']);
		    header("Location: $selfURL&userDeleted=1");
		    exit(0);
		}
		
		// Aktuelle Benutzer suchen, die Zugriff haben
		
		$currentDSBBlock = administrationmodule::getUserListWithAddFunction($selfURL, "DSB", "addUser", "removeUser", "Datenschutzbeauftragte", "", 'Webportal_Datenschutzbeauftragte');
		
		
		
		
		$html = "";
		eval("\$html = \"" . DB::getTPL()->get("datenschutz/admin/index") . "\";");
		
		return $html;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
}


?>