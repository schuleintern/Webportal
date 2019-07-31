<?php




class elternmaillists extends AbstractPage {
	
	private $isMaiLAdmin = false;
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
			"Infomail", "Individuelle Empfängerlisten" 
		) );
		
		$this->checkLogin();
		
		if(!DB::getSession()->isAdmin()) {
			$this->checkAccessWithGroup("Webportal_Elternmail");
		}
			
	}
	
	public function execute() {
		switch($_GET['mode']) {
			default:
				$this->showLists();
			break;
			
			case 'add':
				$this->addList();
			break;
			
			case 'deleteList':
				$this->deleteList();
			break;
			
			case "deleteMember":
				$this->deleteMember();
			break;
			
			case "editList":
				$this->editList();
			break;
		}
	}
	
	private function editList() {
		if($_GET['save'] > 0) {
		
			DB::getDB()->query("DELETE FROM elternmail_groups WHERE groupName='" . DB::getDB()->escapeString($_REQUEST['listName']) . "'");
			
			$users = DB::getDB()->query("SELECT userID FROM users");
			while($u = DB::getDB()->fetch_array($users)) {
				if($_POST['user_' . $u['userID']] > 0) {
					DB::getDB()->query("INSERT INTO elternmail_groups (groupName, userID) values('" . DB::getDB()->escapeString($_REQUEST['listName']) . "','" . $u['userID'] . "')");
				}
			}
			header("Location: index.php?page=elternmaillists");
			exit(0);
		}
		else {
			// Formular anzeigen
			
			$userIDs = [];
			
			$members = DB::getDB()->query("SELECT userID FROM elternmail_groups WHERE groupName='" . DB::getDB()->escapeString($_REQUEST['listName']) . "'");
			while($m = DB::getDB()->fetch_array($members)) $userIDs[] = $m['userID'];
				
			$userTabs = $this->getUserLists($userIDs);
				
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/lists/edit") . "\");");
		}
	}
	
	private function deleteMember() {
		DB::getDB()->query("DELETE FROM elternmail_groups WHERE groupName='" . DB::getDB()->escapeString($_GET['listName']) . "' AND userID='" . DB::getDB()->escapeString($_GET['userID']) . "'");
		header("Location: index.php?page=elternmaillists");
	}
	
	private function deleteList() {
		DB::getDB()->query("DELETE FROM elternmail_groups WHERE groupName='" . DB::getDB()->escapeString($_GET['listName']) . "'");
		header("Location: index.php?page=elternmaillists");
	}
	
	private function addList() {
		if($_GET['save'] > 0) {
			$users = DB::getDB()->query("SELECT userID FROM users");
			while($u = DB::getDB()->fetch_array($users)) {
				if($_POST['user_' . $u['userID']] > 0) {
					DB::getDB()->query("INSERT INTO elternmail_groups (groupName, userID) values('" . DB::getDB()->escapeString($_POST['newGroupName']) . "','" . $u['userID'] . "')");
				}
			}
			
			header("Location: index.php?page=elternmaillists");
		}
		else {
			// Formular anzeigen
			
			$userTabs = $this->getUserLists([]);
			
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/lists/add") . "\");");
		}
	}

	
	private function getDisplayNameForNetwork($name) {
		if($name == "SCHULEINTERN_ELTERN") return "Eltern";
	
		if($name == "SCHULEINTERN_LEHRER") return "Lehrer";
	
		if($name == "SCHULEINTERN_SCHUELER") return "Schüler";
	
		if($name == "SCHULEINTERN") return "Manuell erstellte Benutzer";
	
		return $name . " (Synchronisiert)";
	}
	
	private function getUserLists($selectedUserIDs) {
	
		$tabs = "";
	
		$network = DB::getDB()->escapeString($_GET['network']);
	
		$networks = DB::getDB()->query("SELECT DISTINCT userNetwork FROM users ORDER BY userNetwork ASC");
	
		$first = true;
		
		$allNetworks = [];
		while($net = DB::getDB()->fetch_array($networks)) {
			if($first) {
				if($network == "") $network = $net['userNetwork'];
	
				$first = false;
			}
			
			$allNetworks[] = $net['userNetwork'];
			
			$tabs .= "<li" . (($net['userNetwork'] == $network) ? " class=\"active\"" : "") . "><a href=\"#" . $net['userNetwork'] . "\" data-toggle=\"tab\"><i class=\"fa fa-users\"></i> " . $this->getDisplayNameForNetwork($net['userNetwork']) . "</a></li>\r\n";
		}
		
		$tabContents = "";
		
		for($i = 0; $i < sizeof($allNetworks); $i++) {
			$users = DB::getDB()->query("SELECT * FROM users LEFT JOIN schueler ON schuelerUserID=userID LEFT JOIN lehrer ON lehrerUserID=userID WHERE userNetwork = '" . $allNetworks[$i] . "' ORDER BY userNetwork ASC, userName");
			
			$tabIsActive = (($i == 0) ? (" active") : (""));
			
			$userHTML = "";
			while($user = DB::getDB()->fetch_array($users)) {
				eval("\$userHTML .= \"" . DB::getTPL()->get("elternmail/lists/user_bit") . "\";");
			}
			
			eval("\$tabContents .= \"" . DB::getTPL()->get("elternmail/lists/userlist_tab") . "\";");
		}

		eval("\$html = \"" . DB::getTPL()->get("elternmail/lists/userlist") . "\";");
		
		return $html;
	}
	
	private function showLists() {
		$allLists = DB::getDB()->query("SELECT DISTINCT groupName FROM elternmail_groups ORDER BY groupName ASC");
		
		$listHTML = "";
		while($list = DB::getDB()->fetch_array($allLists)) {
			$members = DB::getDB()->query("SELECT * FROM users WHERE userID IN (SELECT userID FROM elternmail_groups WHERE groupName='" . $list['groupName'] . "') ORDER BY userName ASC, userLastName ASC, userFirstName ASC");
			
			$mem = "";
			
			while($m = DB::getDB()->fetch_array($members)) {
				$mem .= $m['userName'] . " (" . $m['userFirstName'] . " " . $m['userLastName'] . ") <a href=\"index.php?page=elternmaillists&mode=deleteMember&listName=" . urlencode($list['groupName']) . "&userID=" . $m['userID'] . "\"><i class=\"fa fa-trash\"></i></a><br />";
			}
			eval("\$listHTML .= \"" . DB::getTPL()->get("elternmail/lists/bit") . "\";");
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/lists/index") . "\");");
	}
	
	
	public static function getNotifyItems() {
		return array();
	}
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSiteDisplayName() {
		return "Infomail: Eigene Listen";
	}
	
	public static function getSettingsDescription() {
		return array();
	}
	
	public static function getUserGroups() {
		return [];	
	}
	
}

?>