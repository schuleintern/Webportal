<?php

class userprofilemylogins extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Benutzerprofil", "Meine Logins"));
		$this->checkLogin();
	}

	public function execute() {
	    
	    if(!DB::getSession()->getUser()->userCanChangePassword()) {
	        new errorPage("Kein Zugriff auf das Profil.");
	    }
	    
		$groupList = implode(", ",DB::getSession()->getGroupNames());
		$groupList = str_replace("G_","",$groupList);
				
		$data = DB::getSession();
				
		$username = $data->getData("userName");
		$realname = $data->getData("userFirstName") . " " . $data->getData("userLastName");
		
		
		$thisSession = DB::getDB()->query_first("SELECT * FROM sessions WHERE sessionID='" . DB::getSession()->getSessionID() . "'");
		$thisSessionLastActivity = functions::makeDateFromTimestamp($thisSession['sessionLastActivity']);
		$thisSessionIp = $thisSession['sessionIP'];
		$thisSessionIp = explode(".",$thisSessionIp);
		$thisSessionIp[2] = "*";
		$thisSessionIp[3] = "*";
		$thisSessionIp = implode(".",$thisSessionIp);
		
		if($thisSession['sessionDevice'] != "NORMAL") {
			switch($thisSession['sessionDevice']) {
				case "ANDROIDAPP": $thisSessionBrowser = "Android App"; break;
				case "IOSAPP": $thisSessionBrowser = "iOS App"; break;
				case "WINDOWSPHONEAPP": $thisSessionBrowser = "Windows Phone App"; break;
				case "SINGLESIGNON": $thisSessionBrowser = "Automatischer Login vom Schulcomputer"; break;
			}
		}
		else $thisSessionBrowser = $thisSession['sessionBrowser'];
				
		// Gespeicherte Sitzungen
		
		if($_GET['deleteAllSavedSessions'] > 0) {
			DB::getDB()->query("DELETE FROM sessions WHERE sessionUserID='" . DB::getUserID() . "' AND sessionType='SAVED'");
			header("Location: index.php?page=userprofilemylogins");
			exit();
		}
		
		
		$savedSessions = DB::getDB()->query("SELECT * FROM sessions WHERE sessionUserID='" . DB::getUserID() . "' AND sessionType='SAVED'");
		
		$sessionHTML = "";
		while($session = DB::getDB()->fetch_array($savedSessions)) {
			
			$deleteKey = md5("##" . $session['sessionID'] . "MySecretDeleteSalt :-) ");
			
			if(isset($_GET['deleteSession']) && $_GET['deleteSession'] == $deleteKey) {
				DB::getDB()->query("DELETE FROM sessions WHERE sessionID='" . $session['sessionID'] . "'");
			}
			else {
				$sessionLastActivity = functions::makeDateFromTimestamp($session['sessionLastActivity']);
				$sessionIp = $session['sessionIP'];
				$sessionIp = explode(".",$sessionIp);
				$sessionIp[2] = "*";
				$sessionIp[3] = "*";
				$sessionIp = implode(".",$sessionIp);
				
				if($session['sessionDevice'] != "NORMAL") {
					switch($session['sessionDevice']) {
						case "ANDROIDAPP": $sessionBrowser = "<i class=\"fa fa-android\"></i> Android App"; break;
						case "IOSAPP": $sessionBrowser = "<i class=\"fa fa-apple\"></i> iOS App"; break;
						case "WINDOWSPHONEAPP": $sessionBrowser = "<i class=\"fa fa-windows\"></i> Windows Phone App"; break;
						case "SINGLESIGNON": $sessionBrowser = "<i class=\"fa fa-key\"></i> Automatischer Login vom Schulcomputer"; break;
					}
				}
				else $sessionBrowser = $session['sessionBrowser'];
				
				$thisSessionBrowser = $session['sessionBrowser'];
				
				$sessionHTML .=  "<tr>";
				$sessionHTML .=  "<td>$sessionLastActivity</td>";
				$sessionHTML .=  "<td>$sessionIp</td>";
				$sessionHTML .=  "<td>$sessionBrowser</td>";
				$sessionHTML .=  "<td><i class=\"fa fa-sign-out\"></i> <a href=\"index.php?page=userprofilemylogins&deleteSession=$deleteKey\">Sitzung Beenden</a>";
				$sessionHTML .=  "</tr>";
			}
			
		}
				
		eval("echo(\"".DB::getTPL()->get("userprofile/mylogins")."\");");
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
		return 'Eigene aktive Sitzungen verwalten';
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
}


?>