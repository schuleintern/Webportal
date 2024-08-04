<?php


class AdministrationEltern extends AbstractPage {

	private $info;
	
	private static $allGroups = array();
	private static $currentGroups = array();
	private static $groupBeschreibungen = array();
	
	private static $showMessage = "";
	
	public function __construct() {
		parent::__construct([]);

		// Nur Administration

		new errorPage();
	}

	public function execute() {}
	
	public static function displayAdministration($selfURL) {
		return self::doIT($selfURL);
	}
	
	private static function doIT($selfURL) {
		
		switch($_GET['action']) {

            case 'getElternData':
                $user = user::getUserByID($_REQUEST['userID']);

                $result = ['success' => false];

                if ($user === null || !$user->isEltern()) {
                    $result['error'] = 'not found';
                } else {
                    $result['success'] = true;
                    $result['userid'] = $user->getUserID();
                    $result['username'] = $user->getUserName();
                    $result['isAdmin'] = $user->isAdmin();
                    $result['kindEditable'] = DB::getGlobalSettings()->elternUserMode == 'ASV_CODE';

                    $result['kinder'] = [];

                    $kinder = $user->getElternObject()->getMySchueler();


                    for ($i = 0; $i < sizeof($kinder); $i++) {
                        $result['kinder'][] = [
                            'name' => $kinder[$i]->getCompleteSchuelerName(),
                            'klasse' => $kinder[$i]->getKlasse(),
                            'asvID' => $kinder[$i]->getAsvID()
                        ];
                    }

                }

                header("Content-type: application/json");
                echo(json_encode($result));
                exit(0);
                break;

            case 'removeKind':
                $user = user::getUserByID($_REQUEST['userID']);

                $result = ['success' => false];

                if ($user === null || !$user->isEltern()) {
                    $result['error'] = 'not found';
                }
                else if(DB::getGlobalSettings()->elternUserMode != 'ASV_CODE') {
                    $result['error'] = 'not valid. Wrong elternusermode';
                }
                else {

                    $alleSchueler = $user->getElternObject()->getMySchueler();

                    for($i = 0; $i < sizeof($alleSchueler); $i++) {
                        if($alleSchueler[$i]->getAsvID() == $_REQUEST['asvID']) {
                            $user->getElternObject()->removeSchueler($alleSchueler[$i]);
                            break;
                        }
                    }

                    $result['success'] = true;
                }

                header("Content-type: application/json");
                echo(json_encode($result));
                exit(0);
            break;

            case 'addKind':
                $user = user::getUserByID($_REQUEST['userID']);

                $result = ['success' => false];

                if ($user === null || !$user->isEltern()) {
                    $result['error'] = 'not found';
                }
                else if(DB::getGlobalSettings()->elternUserMode != 'ASV_CODE') {
                    $result['error'] = 'not valid. Wrong elternusermode';
                }
                else {

                    $schueler = schueler::getByAsvID($_REQUEST['asvID']);

                    if($schueler != null) {
                        $user->getElternObject()->addSchueler($schueler);

                        $result['success'] = true;
                    }
                    else {
                        $result['success'] = false;
                    }

                }

                header("Content-type: application/json");
                echo(json_encode($result));
                exit(0);
                break;

            case 'toggleAdminStatus':
                $user = user::getUserByID($_REQUEST['userID']);

                $result = ['success' => false];

                if ($user === null || !$user->isEltern()) {
                    $result['error'] = 'not found';
                } else {
                    $usergroup = usergroup::getGroupByName("Webportal_Administrator");

                    if($user->isAdmin()) {
                        $usergroup->removeUser($user->getUserID());
                    }
                    else {
                        $usergroup->addUser($user->getUserID());
                    }
                    $result = ['success' => !false];
                }

                header("Content-type: application/json");
                echo(json_encode($result));
                exit(0);
                break;

            case 'ajaxSearchSchueler':

                $term = DB::getDB()->escapeString($_REQUEST['term']);
                header("Content-type: text/plain");


                $data = [];

                if(strlen($term) >= 2) {
                    $users = DB::getDB()->query("SELECT schuelerAsvID, schuelerName, schuelerRufname, schuelerVornamen, schuelerKlasse FROM schueler WHERE 

                schuelerName LIKE '%" . $term . "%' OR schuelerRufname LIKE '%" . $term . "%' OR schuelerVornamen LIKE '%" . $term . "%'");

                    $first = true;

                    while($user = DB::getDB()->fetch_array($users)) {
                        $data[] = [
                            'id' =>   $user['schuelerAsvID'],
                            'value' => $user['schuelerAsvID'],
                            'label' => $user['schuelerKlasse'] . ": " . $user['schuelerName'] . ", " . $user['schuelerRufname']
                        ];
                    }
                }

                echo(json_encode($data));


                exit(0);
                break;

			case 'addUserAsAdmin':
				if(DB::isSchulnummern(9400) && DB::getUserID() != 1) {
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
				
				if(DB::isSchulnummern(9400) && DB::getUserID() != 1) {
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
				if(DB::isSchulnummern(9400)) {
					new errorPage("In der Demo Version ist keine Passwort Änderung möglich!");
				}
				
				$user = user::getUserByID(intval($_REQUEST['userID']));

				if($user != null) {
                    if($user->isEltern()) {
                        $user->setPassword($_REQUEST['password']);

                        self::$showMessage = "Passwort wurde geändert für " . $user->getUserName() . "";
                    }
                    else {
                        self::$showMessage = "Ist kein Elternbenutzer: " . $user->getUserName() . "";
                    }

                }
				else {
                    self::$showMessage = "Nicht vorhanden: " . $_REQUEST['userID'] . "";
				}

				return self::showIndex($selfURL);
			break;

			case "deleteUser":
			    $user = user::getUserByID(intval($_GET['userID']) );

			    if($user->isEltern()) {
			        if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
                        $user->deleteUser();
                    }
			        else {
			            new errorPage("Elternbenutzer können nur gelöscht werden, in dem sie aus der ASV entfernt werden.");
                    }
                }
			    else {
			        new errorPage("Benutzer ist nicht Eltern.");
                }

				self::$showMessage = "Der Benutzer wurde gelöscht";

				return self::showIndex($selfURL);
			break;
				
			default:
				return self::showIndex($selfURL);
			break;
		}
	}
	
	private static function showIndex($selfURL) {

		$tabs = "";

		$network = "SCHULEINTERN_ELTERN";

		$users = user::getAllEltern();
		
		$userHTML = "";
		for($i = 0; $i < sizeof($users); $i++) {
		    if($users[$i]->isEltern()) {

                $isAdmin = $users[$i]->isAdmin();

                $zugeordneteKinder = "";

                $kinder = $users[$i]->getElternObject()->getMySchueler();

                for ($k = 0; $k < sizeof($kinder); $k++) {
                    $zugeordneteKinder .= $kinder[$k]->getCompleteSchuelerName() . " (Klasse " . $kinder[$k]->getKlasse() . ")<br />";
                }

                $canEdit = DB::getGlobalSettings()->elternUserMode == 'ASV_CODE';

                eval("\$userHTML .= \"" . DB::getTPL()->get("administration/eltern/user_bit") . "\";");
            }
		}
		
		if(self::$showMessage != "") {
			self::$showMessage = "<div class=\"callout callout-info\">" . self::$showMessage . "</div>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("administration/eltern/index") . "\";");
		
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
		if(DB::getGlobalSettings()->elternUserMode == 'ASV_MAIL') {
			return array(
			array(
					"name" => "elternmail-create-users",
					"typ" => "BOOLEAN",
					"titel" => "Neue Elternbenutzer erstellen?",
					"text" => "Solange diese Option nicht aktiv ist, werden neue Elternbenutzer nicht erstellt."
			),
			array(
					"name" => "elternmail-subjectnewuser",
					"typ" => "ZEILE",
					"titel" => "Neuer Elternbenutzer - Betreff",
					"text" => "Betreff der E-Mail an die Eltern, die sich neu registriert haben."
			),
			array(
					"name" => "elternmail-textnewuser",
					"typ" => "TEXT",
					"titel" => "Neuer Elternbenutzer - Text",
					"text" => "Text der E-Mail an die Eltern, die sich neu registriert haben.<br />Platzhalter: Benutzername: {BENUTZERNAME}<br />Passwort: {PASSWORT}"
			));
		}
		else return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Elternbenutzer';
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