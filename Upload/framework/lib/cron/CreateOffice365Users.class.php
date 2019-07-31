<?php


class CreateOffice365Users extends AbstractCron {

	private $result = '';
	
	public function __construct() {
	}

	public function execute() {
		// Schritt 1:
		// Für jeden fehlenden Benutzer, einen Benutzer erstellen.
		
	    if(DB::getSettings()->getBoolean('office365-active')) {
	        
	        $licenses = Office365Api::getLicenseStatus();
	        
	        $licenseIDs = [];
	        
	        for($i = 0; $i < sizeof($licenses); $i++) {
	            $licenseIDs[$licenses[$i]['name']] = $licenses[$i]['id'];
	        }
	        	        
	        
	       if(DB::getSettings()->getBoolean('office365-schueler-createusers')) {
	           $licenses = [];
	           
	           for($i = 1; $i <= 3; $i++) {
	               if(DB::getSettings()->getValue('office365-schueler-license' . $i) != "")
	                   $licenses[] = $licenseIDs[DB::getSettings()->getValue('office365-schueler-license' . $i)];
	           }
	           
	           if(sizeof($licenses) == 0) {
	               $this->result .= 'Erstellung von Schülerbenutzern nicht möglich, da keine Lizenzen zugewiesen wurden.';
	           }
	           else {
	               $domain = DB::getSettings()->getValue('office365-schueler-domain');
	               
	               $vorhanden = [];
	               $vorhandenSQL = DB::getDB()->query("SELECT * FROM office365_accounts WHERE accountIsPupil=1");
	               while($v = DB::getDB()->fetch_array($vorhandenSQL)) $vorhanden[] = $v;
	               	               
	               
	               $schueler = schueler::getAll();
	               
	               $createAccountsSchueler = [];
	               $deleteAccountsSchueler = [];
	               
	               // Für jedem Schüler ein Account vorhanden?
	               for($i = 0; $i < sizeof($schueler); $i++) {
	                   if(!$schueler[$i]->isAusgetreten()) {
    	                   $found = false;
    	                   
    	                   for($p = 0; $p < sizeof($vorhanden); $p++) {
    	                       if($vorhanden[$p]['accountAsvID'] == $schueler[$i]->getAsvID()) {
    	                           $found = true;
    	                           break;
    	                       }
    	                   }
    	                   
    	                   if(!$found) {
    	                       $createAccountsSchueler[] = $schueler[$i];
    	                   }
	                   }
	               }
	               
	               
	               // Welche Accounts müssen gelöscht werden?
	               
	               for($i = 0; $i < sizeof($vorhanden); $i++) {
	                   $found = false;
	                   
	                   for($p = 0; $p < sizeof($schueler); $p++) {
	                       if(!$schueler[$p]->isAusgetreten()) {
	                           if($schueler[$p]->getAsvID() == $vorhanden[$i]['accountAsvID']) {
	                               $found = true;
	                           }
	                       }
	                   }
	                   
	                   if(!$found) {
	                       $deleteAccountsSchueler[] = $vorhanden[$i];
	                   }
	               }
	               
	               
	               
	               
	               for($i = 0; $i < sizeof($deleteAccountsSchueler); $i++) {
	                   $this->result .= "Lösche $username\r\n";
	                   
	                   $username = explode("@",$deleteAccountsSchueler[$i]['accountUsername']);
	                   Office365Api::deleteUser($username[0], $username[1]);
	                   
	                   
	                   DB::getDB()->query("DELETE FROM office365_accounts WHERE accountAsvID='" . $deleteAccountsSchueler[$i]['accountAsvID'] . "'");
	               }
	               
	               
	               
	               	               
	               for($i = 0; $i < sizeof($createAccountsSchueler); $i++) {
	                   $user = $createAccountsSchueler[$i]->getUser();
	                   
	                   $newPassword = md5(rand());
	                   $newPassword = substr($newPassword,0,10) . "!";
	                   
	                   if($user != null) {
	                       
	                       $result = Office365Api::createUser($user->getUserName(), $domain, $newPassword, $user->getDisplayName(), $user->getFirstName(), $user->getLastName(), $createAccountsSchueler[$i]->getAsvID());
	                       if($result['statusCode'] == 201) {
	                           // Erstellt
	                           $id = $result['data']->id;
	                           
	                           DB::getDB()->query("INSERT INTO office365_accounts(
                                    accountAsvID,
                                    accountUsername,
                                    accountUserID,
                                    accountInitialPassword,
                                    accountCreated,
                                    accountIsPupil
                                ) values (
                                    '" . DB::getDB()->escapeString($createAccountsSchueler[$i]->getAsvID()) . "',
                                    '" . DB::getDB()->escapeString($user->getUserName() . "@" . $domain) . "',
                                    '" . DB::getDB()->escapeString($id) . "',
                                    '" . DB::getDB()->escapeString($newPassword) . "',
                                    UNIX_TIMESTAMP(),1
                                )");
	                           $this->result .= "Account für Schüler " .$createAccountsSchueler[$i]->getCompleteSchuelerName() . " erstellt.\r\n";
	                       }
	                   }
	                   
	                   if($i == 100) break;     // Maximal 20 Accounts pro Durchgang erstellen
	               }

	               
	               // Noch nicht mit Details versorgte Benutzer
	               
	               $testTime = time() - (3*60); // Erst nach 20 Minuten können Details erstellt werden.
	               
	               $noDetailsSQL = DB::getDB()->query("SELECT * FROM office365_accounts WHERE accountIsPupil=1 AND accountDetailsSet=0 AND accountCreated < $testTime");
	               
	               
	               $maxChange = 0;
	               $noDetailsData = [];
	               while($noDetails = DB::getDB()->fetch_array($noDetailsSQL)) $noDetailsData[] = $noDetails;
	               
	               for($i = 0; $i < sizeof($noDetailsData); $i++) {
	                   $noDetails = $noDetailsData[$i];
	                   
	                   $username = explode("@",$noDetails['accountUsername']);
	                   
	                   // Debugger::debugObject($username);
	                   
	                   Office365Api::setAsvID($username[0], $username[1], $username['accountAsvID']);
	                   Office365Api::addLicensesToUser($username[0], $username[1], $licenses);
	                   Office365Api::addUserToGroup($username[0], $username[1], DB::getSettings()->getValue('office365-schueler-groupid'));
	                   
	                   DB::getDB()->query("UPDATE office365_accounts SET accountDetailsSet=1, accountLicenseSet=1 WHERE accountIsPupil=1 AND accountAsvID='" . $noDetails['accountAsvID'] . "'");
	               
	                   $this->result .= $noDetails['accountUsername'] . " lizenziert.\r\n";
	                   $this->result .= $noDetails['accountUsername'] . " mit der ASV ID versorgt.\r\n";
	                   $this->result .= $noDetails['accountUsername'] . " der Benutzergruppe hinzugefügt.\r\n";
	               
	                   $maxChange++;
	                   if($maxChange == 10) break;
	               }
	               
	           }
	           
	           
	       }
	       
	       if(DB::getSettings()->getBoolean('office365-lehrer-createusers')) {
	           $licenses = [];
	           
	           for($i = 1; $i <= 3; $i++) {
	               if(DB::getSettings()->getValue('office365-lehrer-license' . $i) != "")
	                   $licenses[] = $licenseIDs[DB::getSettings()->getValue('office365-lehrer-license' . $i)];
	           }
	           
	           if(sizeof($licenses) == 0) {
	               $this->result .= 'Erstellung von Lehrerbenutzern nicht möglich, da keine Lizenzen zugewiesen wurden.';
	           }
	           else {
	               $domain = DB::getSettings()->getValue('office365-lehrer-domain');
	               
	               $vorhanden = [];
	               $vorhandenSQL = DB::getDB()->query("SELECT * FROM office365_accounts WHERE accountIsTeacher=1");
	               while($v = DB::getDB()->fetch_array($vorhandenSQL)) $vorhanden[] = $v;
	               
	               
	               $lehrer = lehrer::getAll();
	               
	               $createAccountsLehrer = [];
	               $deleteAccountsLehrer = [];
	               
	               // Für jedem Schüler ein Account vorhanden?
	               for($i = 0; $i < sizeof($lehrer); $i++) {
	                       $found = false;
	                       
	                       for($p = 0; $p < sizeof($vorhanden); $p++) {
	                           if($vorhanden[$p]['accountAsvID'] == $lehrer[$i]->getAsvID()) {
	                               $found = true;
	                               break;
	                           }
	                       }
	                       
	                       if(!$found) {
	                           $createAccountsLehrer[] = $lehrer[$i];
	                       }
	               }
	               
	               
	               // Welche Accounts müssen gelöscht werden?
	               
	               for($i = 0; $i < sizeof($vorhanden); $i++) {
	                   $found = false;
	                   
	                   for($p = 0; $p < sizeof($lehrer); $p++) {
	                           if($lehrer[$p]->getAsvID() == $vorhanden[$i]['accountAsvID']) {
	                               $found = true;
	                           }
	                   }
	                   
	                   if(!$found) {
	                       $deleteAccountsLehrer[] = $vorhanden[$i];
	                   }
	               }
	               
	               
	               // Debugger::debugObject($createAccountsSchueler,1);
	               
	               for($i = 0; $i < sizeof($deleteAccountsLehrer); $i++) {
	                   $username = explode("@",$deleteAccountsLehrer[$i]['accountUsername']);
	                   
	                   Office365Api::deleteUser($username[0], $username[1]);
	                   
	                   DB::getDB()->query("DELETE FROM office365_accounts WHERE accountAsvID='" . $deleteAccountsLehrer[$i]['accountAsvID'] . "'");
	               }
	               
	               
	               for($i = 0; $i < sizeof($createAccountsLehrer); $i++) {
	                   $user = $createAccountsLehrer[$i]->getUser();
	                   
	                   $newPassword = md5(rand());
	                   $newPassword = substr($newPassword,0,10) . "!";
	                   
	                   if($user != null) {
	                       
	                       $username = $user->getUserName();
	                       
	                       $username = strtolower($username);
	                       
	                       $username = str_replace("ö","oe",$username);
	                       $username = str_replace("ä","ae",$username);
	                       $username = str_replace("ü","ue",$username);
	                       $username = str_replace("ß","ss",$username);
	                       
	                       $result = Office365Api::createUser($username, $domain, $newPassword, $user->getDisplayName(), $user->getFirstName(), $user->getLastName(), $createAccountsLehrer[$i]->getAsvID());
	                       if($result['statusCode'] == 201) {
	                           // Erstellt
	                           $id = $result['data']->id;
	                           
	                           DB::getDB()->query("INSERT INTO office365_accounts(
                                    accountAsvID,
                                    accountUsername,
                                    accountUserID,
                                    accountInitialPassword,
                                    accountCreated,
                                    accountIsTeacher
                                ) values (
                                    '" . DB::getDB()->escapeString($createAccountsLehrer[$i]->getAsvID()) . "',
                                    '" . DB::getDB()->escapeString($username . "@" . $domain) . "',
                                    '" . DB::getDB()->escapeString($id) . "',
                                    '" . DB::getDB()->escapeString($newPassword) . "',
                                    UNIX_TIMESTAMP(),1
                                )");
	                           $this->result .= "Account für Lehrer " .$createAccountsLehrer[$i]->getDisplayNameMitAmtsbezeichnung() . " erstellt.\r\n";
	                       }
	                   }
	                   
	                   if($i == 100) break;     // Maximal 20 Accounts pro Durchgang erstellen
	               }
	               

	               
	               // Noch nicht mit Details versorgte Benutzer
	               
	               $testTime = time() - (3*60); // Erst nach 3 Minuten können Details erstellt werden.
	               
	               $noDetailsSQL = DB::getDB()->query("SELECT * FROM office365_accounts WHERE accountIsTeacher=1 AND accountDetailsSet=0 AND accountCreated < $testTime");
	               
	               
	               $maxChange = 0;
	               $noDetailsData = [];
	               while($noDetails = DB::getDB()->fetch_array($noDetailsSQL)) $noDetailsData[] = $noDetails;
	               
	               for($i = 0; $i < sizeof($noDetailsData); $i++) {
	                   $noDetails = $noDetailsData[$i];
	                   
	                   $username = explode("@",$noDetails['accountUsername']);
	                   
	                   // Debugger::debugObject($username);
	                   
	                   Office365Api::setAsvID($username[0], $username[1], $username['accountAsvID']);
	                   Office365Api::addLicensesToUser($username[0], $username[1], $licenses);
	                   Office365Api::addUserToGroup($username[0], $username[1], DB::getSettings()->getValue('office365-lehrer-groupid'));
	                   
	                   DB::getDB()->query("UPDATE office365_accounts SET accountDetailsSet=1, accountLicenseSet=1 WHERE accountIsTeacher=1 AND accountAsvID='" . $noDetails['accountAsvID'] . "'");
	                   
	                   $this->result .= $noDetails['accountUsername'] . " lizenziert.\r\n";
	                   $this->result .= $noDetails['accountUsername'] . " mit der ASV ID versorgt.\r\n";
	                   $this->result .= $noDetails['accountUsername'] . " der Benutzergruppe hinzugefügt.\r\n";
	                   
	                   $maxChange++;
	                   if($maxChange == 100) break;
	               }
	               
	           }
	           
	           
	       }
	    }
	    else {
	        $this->result = 'Nicht aktiv.';
	    }
	    
	    
	}
	
	
	public function getName() {
		return "Office 365 - Benutzer erstellen";
	}
	
	public function getDescription() {
		return "Erstellt Office 365 Benutzer für jeden Benutzer, falls aktiviert.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
    	return [
	    	'success' => true,
	    	'resultText' => $this->result
	   	];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 1800; // Alle 30 Minuten ausführen.
	}
}



?>