<?php


class administrationusersync extends AbstractPage {

	private $info;

	public function __construct() {
		parent::__construct(array("Administration", "Benutzersynchronisation"));

		$this->checkLogin();

		new errorPage();
	}
	

	public function execute() {}
	
	public static function displayAdministration($selfURL) {	
		switch($_GET['action']) {
			default:
				return self::usersync($selfURL);
			break;
			
			case 'addUserSync':
				return self::addUserSync($selfURL);
			break;

			case 'deleteUserSync':
				return self::deleteUserSync($selfURL);
			break;

			case 'editUserSync':
				return self::editUserSync($selfURL);
			break;
			
			case 'doUserSync':
			    return self::doUserSync($selfURL);
			break;
		}
	}

	private static function usersync($selfURL) {
		$userSyncs = DB::getDB()->query("SELECT * FROM remote_usersync");

		$syncHTML = "";
		while($userSync = DB::getDB()->fetch_array($userSyncs)) {
			eval("\$syncHTML .= \"" . DB::getTPL()->get("administration/usersync/bit") . "\";");
		}

		eval("\$html = \"" . DB::getTPL()->get("administration/usersync/index") . "\";");
		return $html;
	}
	
	private static function doUserSync($selfURL) {
	    
	    $cron = new SyncUsers();
	    $cron->doManualUserSync($_GET['syncID']);
	    
	    $result = $cron->getResult();
	    $messages = nl2br($cron->getMessages());
	    
	    eval("\$html = \"" . DB::getTPL()->get("administration/usersync/syncDone") . "\";");
	    
	    return $html;
	    	    
	}

	private static function deleteUserSync($selfURL) {
		DB::getDB()->query("DELETE FROM remote_usersync WHERE syncID='" . DB::getDB()->escapeString($_GET['syncID']) . "'");
		// TODO: alle Synchronisierten Benutzer lï¿½schen und alle Seiten benachrichtigen

		header("Location: $selfURL&mode=userSync");
		exit(0);
	}

	private static function editUserSync($selfURL) {
		$userSync = DB::getDB()->query_first("SELECT * FROM remote_usersync WHERE syncID='" . DB::getDB()->escapeString($_GET['syncID']) . "'");
		if($userSync['syncID'] > 0) {
			if($_GET['doSave'] > 0) {
				DB::getDB()->query("UPDATE remote_usersync SET
						syncName='" . DB::getDB()->escapeString($_POST['syncName']) . "',
						syncLoginDomain='" . DB::getDB()->escapeString($_POST['syncLoginDomain']) . "',
						syncSecret='" . DB::getDB()->escapeString($_POST['syncSecret']) . "',
						syncURL='" . DB::getDB()->escapeString($_POST['syncURL']) . "',
						syncIsActive='" . DB::getDB()->escapeString($_POST['syncIsActive']) . "',
						syncDirType='" . DB::getDB()->escapeString($_POST['syncDirType']) . "'
						WHERE syncID='" . $userSync['syncID'] . "'");

				header("Location: $selfURL&mode=userSync");
				exit(0);
			}

			$html = "";
			
			eval("\$html = \"" . DB::getTPL()->get("administration/usersync/edit") . "\";");
			return $html;
			
		}
		else {
			new errorPage("Die Sync ID ist nicht vorhanden!");
			exit(0);
		}

	}

	private static function addUserSync($selfURL) {
		DB::getDB()->query("INSERT INTO remote_usersync
				(
					syncName,
					syncLoginDomain,
					syncSecret,
					syncURL,
					syncIsActive,
					syncDirType
				)
				values(
					'" . DB::getDB()->escapeString($_POST['syncName']) . "',
					'" . DB::getDB()->escapeString($_POST['syncLoginDomain']) . "',
					'" . DB::getDB()->escapeString($_POST['syncSecret']) . "',
					'" . DB::getDB()->escapeString($_POST['syncURL']) . "',
					'" . DB::getDB()->escapeString($_POST['syncIsActive']) . "',
					'" . DB::getDB()->escapeString($_POST['syncDirType']) . "'
				)
		");

		header("Location: $selfURL&mode=userSync");
		exit(0);
	}

	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Fï¿½r die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();

	}

	public static function hasSettings() {
		return false;
	}

	public static function getSiteDisplayName() {
		return "Benutzersynchronisation";
	}

	public static function getSettingsDescription() {
		return [];
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
		return 'fa fa-users';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-users';
	}
}


?>