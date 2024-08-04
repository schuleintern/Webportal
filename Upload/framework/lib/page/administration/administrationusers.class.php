<?php


class administrationusers extends AbstractPage {

	private $info;
	
	private static $allGroups = array();
	private static $currentGroups = array();
	private static $groupBeschreibungen = array();
	
	private static $showMessage = "";
	
	public function __construct() {
		parent::__construct(array("Administration", "Benutzeradministration"));
		
		self::checkLogin();
		
		new errorPage();
	}

	public function execute() {}
	
	public static function displayAdministration($selfURL) {
		$pages = requesthandler::getAllowedActions();
		
		for($i = 0; $i < sizeof($pages); $i++)  {
				
			$site = $pages[$i];
			if($site != "error") {
				$classOK = false;
		
				if(class_exists($site)) {
					$classOK = !false;
				}
				else if(class_exists($site . "Page")) {
					$site = $site . "Page";
					$classOK = !false;
				}
		
				if($classOK) {
					$groups = $site::getUserGroups();
					for($g = 0; $g < sizeof($groups); $g++) {
						self::$allGroups[] = $groups[$g];
						self::$groupBeschreibungen[$groups[$g]['groupName']] = $groups[$g]['beschreibung'];
					}
				}
			}
		}
				
		return self::doIT($selfURL);
	}
	
	private static function doIT($selfURL) {
		
		switch($_GET['action']) {
		    
		    case 'exportUsers':
		        $data = [];
		        
		        $users = user::getAll();
		        
		        for($i = 0 ; $i < sizeof($users); $i++) {
		            $user = [];
		            
		            $user['userName'] = $users[$i]->getUserName();
		            $user['userGivenName'] = $users[$i]->getFirstName();
		            $user['userLastName'] = $users[$i]->getLastName();
		            
		            $user['userEMail'] = $users[$i]->getEMail();
		            $user['userPassword'] = $users[$i]->getPasswordHash();
		            
		            $user['isTeacher'] = $users[$i]->isTeacher();
		            $user['isParent'] = $users[$i]->isEltern();
		            $user['isStudent'] = $users[$i]->isPupil();
		            
		            $teacher = NULL;
		            $pupil = NULL;
		            $parent = NULL;
		            
		            if($users[$i]->isTeacher()) {
		                $teacher['teacherAsvID'] = $users[$i]->getTeacherObject()->getAsvID();
		                $teacher['teacherAcronym'] = $users[$i]->getTeacherObject()->getKuerzel();
		            }
		            
		            if($users[$i]->isPupil()) {
		                $pupil['studentAsvID'] = $users[$i]->getPupilObject()->getAsvID();
		            }
		            
		            if($users[$i]->isEltern()) {
		                $parent['parentChildASVIDs'] = $users[$i]->getElternObject()->getMySchuelerAsvIDs();
		            }
		            
		            $user['teacherInfo'] = $teacher;
		            $user['studentInfo'] = $pupil;
		            $user['parentInfo'] = $parent;
		            
		            $data[] = $user;
		            
		        }
		        
		        
		    
		        header("Content-type: application/json");
		        header("Content-Disposition: attachment; filename=Benutzerexport.json");
		        
		        
		        
		        echo(json_encode($data));
		        exit(0);
		        
		    break;
		    
			case 'addUserAsAdmin':
				if(!DB::checkDemoAccess() && DB::getUserID() != 1) {
					new errorPage("In der Demo Version ist keine Änderung der Gruppen möglich!");
				}
								
				$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
				if($user['userID'] > 0) {
					DB::getDB()->query("INSERT INTO users_groups (userID,groupName) values('" . intval($_GET['userID']) . "','Webportal_Administrator') ON DUPLICATE KEY UPDATE groupName=groupName");
					self::$showMessage = "Der Benutzer \"" . $user['userName'] . "\" wurde erfolgreich als Administrator hinzugefügt!";
				}
				
				return self::showIndex($selfURL);
				
				break;
				
			case 'deleteUserAsAdmin':
				
				if(!DB::checkDemoAccess() && DB::getUserID() != 1) {
					new errorPage("In der Demo Version ist keine Änderung der Gruppen möglich!");
				}
				
				$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
				if($user['userID'] > 0) {
						DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . intval($_GET['userID']) . "' AND groupName='Webportal_Administrator'");
					self::$showMessage = "Der Benutzer \"" . $user['userName'] . "\" wurde erfolgreich als Administrator entfernt!";
				}
				
				return self::showIndex($selfURL);
				
				break;
			
			case 'resetPassword':
				if(!DB::checkDemoAccess()) {
					new errorPage("In der Demo Version ist keine Passwort Änderung möglich!");
				}
				
				$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
				if($user['userID'] > 0) {
					if($user['userRemoteUserID'] == "") {
						DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . login::hash($_POST['password']) . "', userCachedPasswordHashTime=UNIX_TIMESTAMP(), userLastPasswordChangeRemote=UNIX_TIMESTAMP() WHERE userID='" . intval($_GET['userID']) . "'");
						self::$showMessage = "Das Passwort des Benutzers \"" . $user['userName'] . "\" wurde geändert.";
					}
					else {
						new errorPage("Für synchronisierte Benutzer ist keine Passwort Änderung möglich!");
					}
				}
				
				return self::showIndex($selfURL);
			break;
			
			case 'updateMail':
				
				if(!DB::checkDemoAccess()) {
					new errorPage("In der Demo Version ist keine E-Mail Änderung möglich!");
				}
				
				$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
				if($user['userID'] > 0) {
					if($user['userRemoteUserID'] == "") {
						if(filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
							DB::getDB()->query("UPDATE users SET userEMail='" . DB::getDB()->escapeString($_POST['mail']) . "' WHERE userID='" . intval($_GET['userID']) . "'");
						}
						else {
							new errorPage("Die angegebene E-Mailadresse ist ungültig");
						}
					}
					else {
						new errorPage("Für synchronisierte Benutzer ist keine E-Mail Änderung möglich!");
					}
				}
				
				return self::showIndex($selfURL);
			break;

            case 'updateEmployeeID':
                if(!DB::checkDemoAccess()) {
                    new errorPage("In der Demo Version ist keine E-Mail Änderung möglich!");
                }

                $user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
                if($user['userID'] > 0) {
                    if($user['userNetwork'] == 'SCHULEINTERN') {
                        DB::getDB()->query("UPDATE users SET userAsvID='" . DB::getDB()->escapeString($_POST['employeeID']) . "' WHERE userID='" . intval($_GET['userID']) . "'");
                    }
                    else new errorPage("Access Violation");
                }

                return self::showIndex($selfURL);
			
			case 'addUser':
				
				if(DB::checkDemoAccess()) {
					$network = "SCHULEINTERN";
					
					
					$user = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . DB::getDB()->escapeString($_POST['userName']) . "'");
					
					if($user['userID'] > 0) {
						new errorPage("Der Benutzername ist schon vergeben!");
					}
					
					DB::getDB()->query("INSERT INTO users (userName, userFirstName, userLastName, userCachedPasswordHash, userCachedPasswordHashTime, userNetwork, userRemoteUserID,userAsvID,userAutoresponseText) values(
							'" . DB::getDB()->escapeString($_POST['userName']) . "',
							'" . DB::getDB()->escapeString($_POST['firstName']) . "',
							'" . DB::getDB()->escapeString($_POST['lastName']) . "',
							'" . login::hash($_POST['password']) . "',
							UNIX_TIMESTAMP(),
							'SCHULEINTERN',
							'','',''
							
							
					)");
					
					
					self::$showMessage = "Der Benutzer wurde erstellt";
				}
				else self::$showMessage = "In der Demoversion können keine zusätzlichen Benutzer erstellt werden.";
				
				return self::showIndex($selfURL);				
			break;
			
			case "deleteUser":
				$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . intval($_GET['userID']) . "'");
				
				if($user['userID'] > 0 && $user['userNetwork'] == "SCHULEINTERN" && $user['userName'] != "spitschka_admin") {
					DB::getDB()->query("DELETE FROM users_groups WHERE userID='" .  intval($_GET['userID']) . "'");
					DB::getDB()->query("DELETE FROM users WHERE userID='" .  intval($_GET['userID']) . "'");
				}
				
				$user = user::getUserByID($_GET['userID']);
				if($user->isEltern() && DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
				    $user->deleteUser();
				    DB::getDB()->query("DELETE FROM sessions WHERE sessionUserID='" . $user->getUserID() . "'");
				}
				
				self::$showMessage = "Der Benutzer wurde gelöscht";
				
				return self::showIndex($selfURL);
			break;
				
			default:
				return self::showIndex($selfURL);
			break;
		}
	}
	
	private static function getDisplayNameForNetwork($name) {
		if($name == "SCHULEINTERN_ELTERN") return "Eltern";
		
		if($name == "SCHULEINTERN_LEHRER") return "Lehrer";
		
		if($name == "SCHULEINTERN_SCHUELER") return "Schüler";
		
		if($name == "SCHULEINTERN") return "Manuell erstellte Benutzer";
		
		return $name . " (Synchronisiert)";
	}
	
	private static function isNetworkSynced($name) {
		if($name == "SCHULEINTERN_ELTERN") return false;
		
		if($name == "SCHULEINTERN_LEHRER") return false;
		
		if($name == "SCHULEINTERN_SCHUELER") return false;
		
		if($name == "SCHULEINTERN") return false;
		
		return true;
	}
	
	private static function showIndex($selfURL) {
		$allG = DB::getDB()->query("SELECT * FROM users_groups");
		
		
		while($g = DB::getDB()->fetch_array($allG)) {
			if(!is_array(self::$currentGroups[$g['userID']])) self::$currentGroups[$g['userID']] = array();
			self::$currentGroups[$g['userID']][] = $g['groupName'];
		}
		
		
		$allG = DB::getDB()->query("SELECT DISTINCT groupName FROM users_groups ORDER BY groupName ASC");

		$groupSelect = "";
		while($g = DB::getDB()->fetch_array($allG)) {
			$groupSelect .= "<option value=\"" . $g['groupName'] . "\"" . (($_REQUEST['groupName'] == $g['groupName']) ? (" selected=\"selected\"") : ("")) . ">" . self::$groupBeschreibungen[$g['groupName']] . "</option>\r\n";
		}
		
		$tabs = "";
		
		$network = DB::getDB()->escapeString($_GET['network']);
		
		$networks = DB::getDB()->query("SELECT DISTINCT userNetwork FROM users ORDER BY userNetwork ASC");
		
		$first = true;
		while($net = DB::getDB()->fetch_array($networks)) {
			if($first) {
				if($network == "") $network = $net['userNetwork'];
				
				$first = false;
			}
			$tabs .= "<li" . (($net['userNetwork'] == $network) ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationmodule&module=administrationusers&network=" . $net['userNetwork'] . "\"><i class=\"fa fa-users\"></i> " . self::getDisplayNameForNetwork($net['userNetwork']) . "</a></li>\r\n";
		}
		
		// $tabs .= "<li" . (($network == "SCHULEINTERN") ? " class=\"active\"" : "") . "><a href=\"index.php?page=administrationusers&network=SCHULEINTERN\"><i class=\"fa fa-users\"></i> " . self::getDisplayNameForNetwork("SCHULEINTERN") . "</a></li>\r\n";
		
		
				
		$users = DB::getDB()->query("SELECT * FROM users WHERE userNetwork = '" . $network . "' " . (($_REQUEST['groupName'] != "") ? (" AND userID IN (SELECT userID FROM users_groups WHERE groupName='" . DB::getDB()->escapeString($_REQUEST['groupName']) . "')") : ("")) . " ORDER BY userNetwork ASC, userName");
		
		
		$userHTML = "";
		while($user = DB::getDB()->fetch_array($users)) {
			
			$groups = "";
			
			if(is_array(self::$currentGroups[$user['userID']])) {
				for($i = 0; $i < sizeof(self::$currentGroups[$user['userID']]); $i++) {
					$groups .= "<a href=\"#\" data-toggle=\"tooltip\" title=\"" . self::$groupBeschreibungen[self::$currentGroups[$user['userID']][$i]] . "\">" . self::$currentGroups[$user['userID']][$i] . "</a>";
					
					if($user['userID'] == DB::getUserID() && self::$currentGroups[$user['userID']][$i] == "Webportal_Administrator") {
						$groups .= " <a href=\"#\" data-toggle=\"tooltip\" title=\"Man kann sich nicht selbst degradieren\"><i class=\"fa fa-trash\"></i></a><br />";
					}
					else {
						$groups .= " <a href=\"index.php?page=administrationmodule&module=administrationusers&action=deleteUserGroup&userID=" . $user['userID'] . "&groupName=" . self::$currentGroups[$user['userID']][$i] . "\"><i class=\"fa fa-trash\"></i></a><br />";
					}
				}
			}
			
			if(self::$currentGroups[$user['userID']] && in_array("Webportal_Administrator", self::$currentGroups[$user['userID']])) {
				$isAdmin = true;
			}
			else $isAdmin = false;
			
			$selectGroupsHTML = "";
			
			for($i = 0; $i < sizeof(self::$allGroups); $i++) {
				$selectGroupsHTML .= "<option value=\"" . self::$allGroups[$i]['groupName'] . "\">" . self::$groupBeschreibungen[self::$allGroups[$i]['groupName']] . "</option>";
			}

            $isSSO = false;
			if($network == 'SCHULEINTERN_SCHUELER' && oAuth2Auth::ssoSchuelerActive()) {
                $isSSO = true;
            }
            else if($network == 'SCHULEINTERN_LEHRER' && oAuth2Auth::ssoTeacherActive()) {
                $isSSO = true;
            }

            $canChangeEmployeeID = false;

            if($network == 'SCHULEINTERN') $canChangeEmployeeID = true;


			
			eval("\$userHTML .= \"" . DB::getTPL()->get("administration/users/user_bit") . "\";");
		}
		
		if(self::$showMessage != "") {
			self::$showMessage = "<div class=\"callout callout-info\">" . self::$showMessage . "</div>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("administration/users/index") . "\";");
		
		return $html;
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
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Benutzer';
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
		return 'fa fa-users';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-users';
	}

}


?>