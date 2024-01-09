<?php

class AdminInfo extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		//$this->needLicense = false;
		//$this->sitename = "Administration / Allgemeines";
		
		parent::__construct(array("Administration", "Allgemeines, Lizenz"), false, true);
		
		//$this->checkLogin();

		if(!DB::getSession()->isAnyAdmin()) {
			// Nur für Admins
			header("Location: index.php");
			exit(0);
		}
		
	}

	public function execute() {
		}
	
	public static function hasSettings() {
		return false;
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
		return true;
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
        $users = DB::getDB()->query("SELECT userID,sessionLastActivity FROM sessions JOIN users ON sessions.sessionUserID=users.userID WHERE sessionLastActivity > (UNIX_TIMESTAMP()-600)");

        $userList = "";
        $count = 0;
        while($u = DB::getDB()->fetch_array($users)) {
            $count++;

            $user = user::getUserByID($u['userID']);
            $userList .= "<tr><td><strong>";

            $userList .= $user->getDisplayName();


            $userList .= "</strong><br />" . functions::makeDateFromTimestamp($u['sessionLastActivity']) . "</td>";


            if($user->isTeacher()) {
                $userList .= "<td style='width: 20%'><button class='btn btn-app disabled'><i class='fa fa-female'></i></button></td>";
            }

            elseif($user->isPupil()) {
                $userList .= "<td style='width: 20%'><button class='btn btn-app disabled'><i class='fa fa-child'></i></button></td>";
            }

            elseif($user->isEltern()) {
                $userList .= "<td style='width: 20%'><button class='btn btn-app disabled'><i class='fa fa-user-friends'></i></button></td>";
            }

            else {
                $userList .= "<td style='width: 20%'><button class='btn btn-app disabled'><i class='fa fa-question-circle'></i></button></td>";

            }


        }

        // Statistik

        $labels = [];

        $eltern = [];
        $schueler = [];
        $lehrer = [];

        $stat = [];

        switch($_REQUEST['statType']) {
            case 'today':
            default:
                $title = "Eingeloggte Benutzer heute";
                $stat = UserLoginStat::getTodayStat();
                break;

            case 'month':
                $title = "Eingeloggte Benutzer diesen Monat";

                $stat = UserLoginStat::getCurrentMonth();
                break;

            case 'year':
                $title = "Eingeloggte Benutzer im Jahr " . date("Y");

                $stat = UserLoginStat::getYear(date("Y"));
                break;

            case 'lastyear':
                $title = "Eingeloggte Benutzer im Jahr " . (date("Y")-1);

                $stat = UserLoginStat::getYear(date("Y")-1);
                break;

        }

        if(sizeof($stat) > 0) {
            $labels = $stat['labels'];
            $lehrer = $stat['teacherdata'];
            $schueler = $stat['studentdata'];
            $eltern = $stat['parentsdata'];
        }


        $labelsJSONEncoded = json_encode($labels);
        $schuelerJSONEncoded = json_encode($schueler);
        $elternJSONEncoded = json_encode($eltern);
        $lehrerJSONEncoded = json_encode($lehrer);


        eval("\$html = \"" . DB::getTPL()->get("administration/info") . "\";");

        //$html = eval("echo(\"" . DB::getTPL()->get("administration/info") . "\");");

        return $html;
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


    public static function getAdminMenuIcon()
    {
        return 'fa fa-info';
    }
    public static function getAdminMenuGroup() {
        return 'System';
    }
    public static function getSiteDisplayName() {
        return 'Info';
    }

	
}


?>