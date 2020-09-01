<?php


class administrationgroups extends AbstractPage {

	
	public function __construct() {
		
		parent::__construct(array("Administration", "Benutzeradministration"));
		
		self::checkLogin();
		
		new errorPage();
	}

	public function execute() {}
	
	public static function displayAdministration($selfURL) {
		if($_REQUEST['action'] != "" && substr($_REQUEST['action'], 0,3) == 'add') {
		    // Benutzer hinzufügen
		      
		    $gruppenID = substr($_REQUEST['action'],3);
		    
		    $group = usergroup::getGroupByName($gruppenID);
				
				$userlist = $_REQUEST['userID'];
				$userlist = explode(',',$_REQUEST['userID']);

				if($group != null) {
					for($i = 0; $i < count($userlist); $i++) {
						$group->addUser($userlist[$i]);
					}
		    }		
		}
		
		if($_REQUEST['action'] != "" && substr($_REQUEST['action'], 0,3) == 'del') {
		    // Benutzer hinzufügen
		    
		    $gruppenID = substr($_REQUEST['action'],3);
		    
		    $group = usergroup::getGroupByName($gruppenID);
		    
		    if($group != null) {
		        $group->removeUser($_REQUEST['userID']);
		    }
		}
		
		
		if($_REQUEST['action'] == 'editGroup') {
		    // Benutzer hinzufügen
		    
    
		    $group = usergroup::getGroupByChecksum($_REQUEST['groupName']);
		    
		    if($group == null) {
		        die("Nicht da!!");
		        header("Location: $selfURL");
		        exit();
		    }
		    
		    
		    $checksumName = md5($group->getName());
		    
		    $group->setIsMessageRecipient($_REQUEST[$checksumName . "-messagerecipient"]);
		    $group->setCanContactByTeacher($_REQUEST[$checksumName . "-contact-teacher"]);
		    $group->setCanContactByPupil($_REQUEST[$checksumName . "-contact-pupils"]);
		    $group->setCanContactByParents($_REQUEST[$checksumName . "-contact-parents"]);
		    
		    $group->setHasNextCloudShare($_REQUEST[$checksumName . "-contact-nextcloud"]);
		    
		    // Debugger::debugObject($group,1);
		    
		    header("Location: $selfURL");
		    exit();
		}
		
		
		if($_REQUEST['action'] == 'add') {
		    usergroup::addOwnGroup($_REQUEST['name']);
		    header("Location: $selfURL");
		    exit();
		}
		
		if($_REQUEST['action'] == 'delete') {
		    $group = usergroup::getGroupByChecksum($_REQUEST['name']);
		    
		    if($group != null) $group->deleteOwnGroup();
   
		    header("Location: $selfURL");
		    exit();
		}
		    
		
		
		$gruppen = usergroup::getAllOwnGroups();
		
		// getUserListWithAddFunction($selfURL, $name, $actionAdd, $actionDelete, $title, $beschreibung, $userGroup) {
		
		$gruppenHTML = "";
		
		for($i = 0; $i < sizeof($gruppen); $i++) {
		    
		    $membersBox = administrationmodule::getUserListWithAddFunction($selfURL, md5($gruppen[$i]->getName()), 'add' . $gruppen[$i]->getName(), 'del' . $gruppen[$i]->getName(), $gruppen[$i]->getName(), "Mitglieder der Gruppe", $gruppen[$i]->getName(), true);
		    
		    $membersBox2 = administrationmodule::getUserListWithAddFunction($selfURL, md5($gruppen[$i]->getName() . "2"), 'add' . $gruppen[$i]->getName() . "_sender", 'del' . $gruppen[$i]->getName(), $gruppen[$i]->getName() . " (Sendeberechtigte)", "Diese Benutzer dürfen diese Gruppe unabhängig von den allgemeinen Sendebrechtigungen kontaktieren.", $gruppen[$i]->getName() . "_sender");
		    
		    
		    $checksumName = md5($gruppen[$i]->getName());
		    
		    
		    $checked['messageRecipient'] = ($gruppen[$i]->isMessageRecipient() ? ' checked' : '');
		    
		    $checked['lehrer'] = ($gruppen[$i]->canContactByTeacher() ? ' checked' : '');
		    $checked['schueler'] = ($gruppen[$i]->canContactByPupil() ? ' checked' : '');
		    $checked['eltern'] = ($gruppen[$i]->canContactByParents() ? ' checked' : '');
		    
		    $checked['nextcloud'] = ($gruppen[$i]->hasNextCloudShare() ? ' checked' : '');
		    
		    
		    
		    eval("\$gruppenHTML .= \"" . DB::getTPL()->get("administration/groups/bit") . "\";");
		}
		
	    if($gruppenHTML == "") {
	        $gruppenHTML = "<div class=\"callout callout-info\">Bisher keine eigenen Gruppen angelegt.</div>";
	    }
	    
	    $html = "";
	    
	    eval("\$html = \"" . DB::getTPL()->get("administration/groups/index") . "\";");
	    return $html;
	}

	public static function hasSettings() {
		return DB::getGlobalSettings()->elternUserMode == 'ASV_MAIL';
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
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Gruppen';
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
	
	public static function getAdminMenuGroup() {
		return 'Benutzerverwaltung';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-user-group';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-users';
	}

}


?>