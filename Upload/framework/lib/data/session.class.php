<?php


class session {	
	private $data;
	private $groupNames = array();
	
	private $userObject = array();
	
	
	
	public function __construct($data=null) {
		$this->data = $data;
		

		$this->userObject = user::getUserByID($this->data['sessionUserID']);
		
		if($this->userObject == null) {
			$this->delete();
			header("Location: index.php");
			exit(0);
		}		
	}
	
	public function isSavedSession() {
		return $this->data['sessionType'] == "SAVED";
	}
	
	public function update() {
	    
	    if($this->is2FactorActive()) {
	        DB::getDB()->query("UPDATE sessions SET session2FactorActive=UNIX_TIMESTAMP(), sessionLastActivity=UNIX_TIMESTAMP(), sessionIP='" . $_SERVER['REMOTE_ADDR'] . "' WHERE sessionID='" . $this->data['sessionID'] . "'");
	    }
	    	
		else {
		    DB::getDB()->query("UPDATE sessions SET session2FactorActive=0, sessionLastActivity=UNIX_TIMESTAMP(), sessionIP='" . $_SERVER['REMOTE_ADDR'] . "' WHERE sessionID='" . $this->data['sessionID'] . "'");
		}
		
	}
	
	public static function cleanSessions() {
		DB::getDB()->query("DELETE FROM sessions WHERE sessionLastActivity < ".(time()-3600) . " AND sessionType='NORMAL'");
	}
	
	public function getData($index) {
		return $this->userObject->getData($index);
	}
	
	public function getMail() {
		return $this->userObject->getEMail();
	}
	
	public function getGroupNames() {
		return $this->userObject->getGroupNames();
	}
	
	public function isPupil() {
		return $this->userObject->isPupil();
	}
	
	public function getSessionID() {
		return $this->data['sessionID'];
	}
	
	public function isTeacher() {
		return $this->userObject->isTeacher();
	}
	
	public function isAdmin() {
	    
		return $this->userObject->isAdmin();
	}
	
	/**
	 * Überprüft, ob der Benutzer Zugriff zur Administration hat.
	 * @return boolean
	 */
	public function isAnyAdmin() {
		return $this->userObject->isAnyAdmin();
	}

	public function isEltern() {
		return $this->userObject->isEltern();
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM sessions WHERE sessionID='" . $this->data['sessionID'] ."'");
		setcookie("schuleinternsession", null, time()-3600);	// Cookie löschen
	}
	
	public function getUserID() {
		return $this->getUser()->getUserID();
	}

	public function isMember($groupName) {
		return in_array($groupName, $this->getGroupNames());
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isDebugSession() {
	    return $this->data['sessionIsDebug'] > 0;
	}
	
	/**
	 * Aktuelles Benutzerobjekt
	 * @return user
	 */
	public function getUser() {
		return $this->userObject;
	}
	
	/**
	 * @deprecated
	 * --> Teacher Object muss Schulleitung sein!
	 * @return boolean
	 */
	public function isSchulleitung() {
		return in_array("Webportal_Schulleiter", $this->getGroupNames());
	}
	
	/**
	 * 
	 * @throws RuntimeException
	 * @return lehrer
	 */
	public function getTeacherObject() {
		if($this->isTeacher()) return $this->userObject->getTeacherObject();
		else throw new RuntimeException("Internal Error. Teacher Object not availible!");
	}
	
	public function getSchuelerObject() {
		if($this->isPupil()) return $this->userObject->getPupilObject();
		else throw new RuntimeException("Internal Error. Student Object not availible!");
	}
	
	public function getPupilObject() {
		return $this->getSchuelerObject();
	}
	
	/**
	 * 
	 * @throws RuntimeException
	 * @return eltern
	 */
	public function getElternObject() {
		if($this->isEltern())	return $this->userObject->getElternObject();
		else throw new RuntimeException("Internal Error. Parents Object not availible!");
	}
	
	/**
	 * Es ist eine App, wenn nicht "NORMAL" im Device steht.
	 * @return boolean
	 */
	public function isApp() {
		return $this->data['sessionDevice'] != "NORMAL";
	}
	
	/**
	 * Überprüft, ob ein Benutzer mit einem lokalen Verzeichnis synchronisiert wurde.
	 * @return boolean
	 */
	public function isSyncedUser() {
		return $this->userData['userRemoteUserID'] != "";
	}
	
	/**
	 * Ist die 2 Faktorauthentifizierung noch gültig? (Sie ist eine Stunde gültig nach der letzten Aktion.)
	 * @return boolean
	 */
	public function is2FactorActive() {
	    return (time() - $this->data['session2FactorActive']) < 3600;
	}
	
	/**
	 * Setzt die Session auf gültige 2FA
	 */
	public function set2FactorActive(bool $isActive = true) {
	    if($isActive) DB::getDB()->query("UPDATE sessions SET session2FactorActive=UNIX_TIMESTAMP() WHERE sessionID='" . $this->data['sessionID'] . "'");
	    else DB::getDB()->query("UPDATE sessions SET session2FactorActive=0 WHERE sessionID='" . $this->data['sessionID'] . "'");
	}
	
	
	/**
	 * 
	 * @param String $key
	 * @param object $object
	 */
	public function addToSessionStore($key, $object) {
	    $currentStore = json_decode($this->data['sessionStore'], true);
	    $currentStore[$key] = $object;
	    $this->data['sessionStore'] = json_encode($currentStore);
	    DB::getDB()->query("UPDATE sessions SET sessionStore='" . DB::getDB()->escapeString($this->data['sessionStore']) . "' WHERE sessionID='" . $this->data['sessionID'] . "'");
	}
	
	public function getFromSessionStore($key) {
	    $currentStore = json_decode($this->data['sessionStore'], true);
	    return $currentStore[$key];
	}
	
	public function removeFromSessionStore($key) {
	    $currentStore = json_decode($this->data['sessionStore'], true);
	    $currentStore[$key] = null;
	    $this->data['sessionStore'] = json_encode($currentStore);
	    DB::getDB()->query("UPDATE sessions SET sessionStore='" . DB::getDB()->escapeString($this->data['sessionStore']) . "' WHERE sessionID='" . $this->data['sessionID'] . "'");
	    
	}


	public static function loginAndCreateSession($userID, $keepLogin = false) {
        DB::getDB()->query("UPDATE users SET userFailedLoginCount=0 WHERE userID='" . $userID . "'");

        $sessionID = substr(base64_encode(random_bytes(1000)), 0, 220);

        $deviceType = "NORMAL";

        DB::getDB()->query("INSERT INTO sessions 
					(sessionID, sessionUserID, sessionType, sessionIP, sessionLastActivity, sessionBrowser, sessionDevice)
					values
						(
							'" . $sessionID . "',
							'" . $userID . "',
							'" . ($keepLogin ? ("SAVED") : ("NORMAL")) . "',
							'" . $_SERVER['REMOTE_ADDR'] . "',
							UNIX_TIMESTAMP(),
							'" . $_SERVER['HTTP_USER_AGENT'] . "',
							'" . $deviceType . "'
						)
			");

        if(DB::isDebug()) {
            // Im Debug kein Secure Cookie
            setcookie("schuleinternsession", $sessionID, (($keepLogin) ? (time() + 365 * 24 * 60 * 60) : (0)), "/", null, false, true);
        }
        else {
            setcookie("schuleinternsession", $sessionID, (($keepLogin) ? (time() + 365 * 24 * 60 * 60) : (0)), "/", null, true, true);
        }
    }
	
	
}