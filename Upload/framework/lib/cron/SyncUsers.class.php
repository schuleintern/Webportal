<?php



class SyncUsers extends AbstractCron {

	private $userlib;

	private $result = "";
	
	private $messages;

	public function __construct() {

	}

	public function execute() {
		if(DB::getGlobalSettings()->schulnummer == "9400") {
			$this->result = "Kein UserSync in der DEMO Version";
			return;
		}

		include_once("../data/config/userlib.class.php");

		$this->userlib = new userlib();

		header("Content-type: text/plain");

		$sync = DB::getDB()->query_first("SELECT * FROM remote_usersync WHERE syncIsActive=1 AND (syncLastSync <= " . (time() - 3600) . " OR syncSuccessFull=0) ORDER BY syncLastSync ASC");

		if($sync['syncID'] > 0) {
            $this->doSync($sync);
		}
		else {
			$this->result = "Keine Aktion durchgeführt.";
		}


		echo($result);
	}
	
	public function doManualUserSync($syncID) {
	    $sync = DB::getDB()->query_first("SELECT * FROM remote_usersync WHERE syncID='" . DB::getDB()->escapeString($syncID) . "'");
	    if($sync['syncID'] > 0) {
	        $this->doSync($sync);
	    }
	    else {
	        $this->result = "Synchronisation nicht gefunden.";
	    }
	    
	    
	}
	
	/**
	 * 
	 * @param String[] $sync Sync String Objekt
	 */
	private function doSync($sync) {
	    $remoteaccess = new RemoteAccess($sync['syncName']);
	    $users = $remoteaccess->getAllUsers();
	    
	    $result = "";
	    $success = 0;
	    
	    if(!is_object($users)) {
	        

        // $rawResult = $remoteaccess->getAllUsersRaw();
	        
	        $result = "Es konnte keine Verbindung mit der Schule hergestellt werden! Meldung: \$users ist kein Objekt. {$sync['syncName']} / " . $users;
	    
	    

	        
	    }
	    else {
	        $result = "Abholung der Benutzer erfolgreich!<br />";
	        
	        
	        $realElements = [];
	        
	        for($i = 0; $i < sizeof($users->users->user); $i++) {
	            $userid = strval($users->users->user[$i]->username);
	            
	            $found = false;
	            
	            
	            for($x = 0; $x < sizeof($realElements); $x++) {
	                if($userid == strval($realElements[$x]->username)) {
	                    $found = true;
	                    break;
	                }
	                
	            }
	            
	            if(!$found) $realElements[] = $users->users->user[$i];
	        }
	        
	        $result .= sizeof($realElements) . " Benutzer empfangen<br />";
	        
	        $allUsers = array();
	        $allUsersSQL = DB::getDB()->query("SELECT * FROM users WHERE userNetwork='" . $sync['syncName'] . "'");
	        while($u = DB::getDB()->fetch_array($allUsersSQL)) {
	            $allUsers[] = $u;
	        }

	        $deleteUserIDs = array();
	        // Welche Nutze müssen gelöscht werden?
	        for($i = 0; $i < sizeof($allUsers); $i++) {
	            $found = false;
	            for($u = 0; $u < sizeof($users->users->user); $u++) {
	                if($allUsers[$i]['userRemoteUserID'] == $realElements[$u]->userid) {
	                    $found = true;
	                    break;
	                }
	            }
	            
	            if(!$found) {
	                $deleteUserIDs[] = $allUsers[$i]['userID'];
	            }
	        }
	        
	        if(sizeof($deleteUserIDs) > 0) {
	            DB::getDB()->query("DELETE FROM users WHERE userID IN (" . implode(",",$deleteUserIDs) . ")");
	            DB::getDB()->query("DELETE FROM users_groups WHERE userID IN (" . implode(",",$deleteUserIDs) . ")");
	        }
	        
	        $result .= sizeof($deleteUserIDs) . " Benutzer gelöscht.<br />";
	        
	        // Benutzer anlegen / aktualisieren
	        
	        $loginPrefix = $sync['syncLoginDomain'];
	        if($loginPrefix != "") $loginPrefix = '@' . $loginPrefix;
	        
	        $updates = 0;
	        $inserts = array();
	        for($u = 0; $u < sizeof($realElements); $u++) {
	            $found = false;
	            for($i = 0; $i < sizeof($allUsers); $i++) {
	                if($allUsers[$i]['userRemoteUserID'] == $realElements[$u]->userid) {
	                    // Gefunden --> Update
	                    if($sync['syncDirType'] == 'ACTIVEDIRECTORY') {
	                        DB::getDB()->query("UPDATE users SET
										userName='" . DB::getDB()->escapeString($realElements[$u]->username) . $loginPrefix . "',
										userFirstName='" . DB::getDB()->escapeString($realElements[$u]->firstname) . "',
										userLastName='" . DB::getDB()->escapeString($realElements[$u]->lastname) . "',
										userAsvID='" . $realElements[$u]->asvid . "',
										userLastPasswordChangeRemote='" . $realElements[$u]->lastpwdchange . "',
										userEmail='" . DB::getDB()->escapeString($realElements[$u]->mail) . "'
										WHERE userID='" . $allUsers[$i]['userID'] . "'");
	                    }
	                    else {
	                        DB::getDB()->query("UPDATE users SET
										userName='" . DB::getDB()->escapeString($realElements[$u]->username) . $loginPrefix . "',
										userFirstName='" . DB::getDB()->escapeString($realElements[$u]->firstname) . "',
										userLastName='" . DB::getDB()->escapeString($realElements[$u]->lastname) . "',
										userAsvID='',
										userCachedPasswordHash='',
										userCachedPasswordHashTime=0,
										userLastPasswordChangeRemote='0',
										userEmail='" . DB::getDB()->escapeString($realElements[$u]->mail) . "'
										WHERE userID='" . $allUsers[$i]['userID'] . "'");
	                    }
	                    $found = true;
	                    $updates++;
	                    break;
	                }
	                
	            }
	            
	            if(!$found) {
	                $inserts[] = "(
								'" . DB::getDB()->escapeString($realElements[$u]->username) . $loginPrefix . "',
								'" . DB::getDB()->escapeString($realElements[$u]->firstname) . "',
								'" . DB::getDB()->escapeString($realElements[$u]->lastname) . "',
								'',
								0,
								'" . DB::getDB()->escapeString($realElements[$u]->lastpwdchange) . "',
								'" . DB::getDB()->escapeString($sync['syncName']) . "',
								'" . DB::getDB()->escapeString($realElements[$u]->userid) . "',
								'" . DB::getDB()->escapeString($realElements[$u]->asvid) . "',
								'" . DB::getDB()->escapeString($realElements[$u]->mail) . "'
							)";
	                
	            }
	        }
	        
	        if(sizeof($inserts) > 0) {
	            DB::getDB()->query("INSERT INTO `users`
							(
								`userName`,
								`userFirstName`,
								`userLastName`,
								`userCachedPasswordHash`,
								`userCachedPasswordHashTime`,
								`userLastPasswordChangeRemote`,
								`userNetwork`,
								`userRemoteUserID`,
								`userAsvID`,
								`userEMail`
							)
							VALUES " . implode(", ",$inserts) . "");
	        }
	        
	        
	        $result .= sizeof($inserts) . " Benutzer eingefügt.<br />";
	        $result .= $updates . " Benutzer aktualisiert.";
	        
	        $success = 1;
	    }
	    
	    $messages = "";
	    
	    $matcher = new MatchUserFunctions();
	    $messages .= $matcher->matchLehrer();
	    $messages .= $matcher->matchSchueler();
	    
	    $this->messages = $messages;
	    
	    DB::getDB()->query("UPDATE remote_usersync SET syncLastSync=UNIX_TIMESTAMP(), syncSuccessfull=$success, syncLastSyncResult='" . DB::getDB()->escapeString($result) . "' WHERE syncID='" . $sync['syncID'] . "'");
	    
	    $this->result = $result;
	}

	public function getName() {
		return "Benutzer synchronisieren";
	}

	public function getDescription() {
		return "Benutzer werden mit einem onSite Verzeichnis (AD oder eDirectory) synchronisiert.";
	}
	
	public function getResult() {
	    return $this->result;
	}
	
	public function getMessages() {
	    return $this->messages;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see AbstractCron::getCronResult()
	 */
	public function getCronResult() {
		return ['success' => true, 'resultText' => $this->result];
	}

	public function informAdminIfFail() {
		return true;
	}

	public function executeEveryXSeconds() {
		return 180;		// Alle 3 Minuten ausführen.
	}

    public function onlyExecuteSeparate()
    {
        return true;
    }
}



?>