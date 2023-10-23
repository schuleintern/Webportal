<?php

class CreateElternUsers extends AbstractCron {

	private $isSuccess = true;
	private $listDeleted = "";
	private $listCreated = "";
	
	public function __construct() {
	}

	public function execute() {

	    if(DB::getGlobalSettings()->elternUserMode == "KLASSENELTERN") {
	        $currentUsers = user::getAll();
	        
	        $eltern = [];
	        
	        for($i = 0; $i < sizeof($currentUsers); $i++) {
	            if($currentUsers[$i]->isEltern()) {
	                $eltern[] = $currentUsers[$i];
	            }
	        }
	        
	        $klassen = klasse::getAllKlassen();
	        
	        for($i = 0; $i < sizeof($klassen); $i++) {
	            
	            $userNameKlasse = "Eltern-" . $klassen[$i]->getKlassenName();
	            $userNameKlasse = str_replace(" ","_", $userNameKlasse);
	            
	            $sender = DB::getGlobalSettings()->smtpSettings['sender'];
	            $sender = explode("@",$sender)[1];
	            
	            $userNameKlasse = $userNameKlasse . "@" . $sender;
	            

	            $user = user::getByUsername($userNameKlasse);
	            
	            if($user == null) {
	                DB::getDB()->query("INSERT INTO users (userName, userFirstName, userLastName, userCachedPasswordHash, userCachedPasswordHashTime, userNetwork, userCanChangePassword) values(
							'" . DB::getDB()->escapeString($userNameKlasse) . "',
							'" . DB::getDB()->escapeString("Eltern") . "',
							'" . DB::getDB()->escapeString("Klasse " . $klassen[$i]->getKlassenName()) . "',
							'" . login::hash("difu0we48fhwieufhn") . "',
							UNIX_TIMESTAMP(),
							'SCHULEINTERN_ELTERN',
                            0
					)");
	                
	                echo("Neu anlegen: $userNameKlasse");
	                
	                $userID = DB::getDB()->insert_id();
	            }
	            else {
	                $userID = $user->getUserID();
	            }
	            
	            $schueler = $klassen[$i]->getSchueler();
	            
	            
	            DB::getDB()->query("DELETE FROM eltern_email WHERE elternEMail='" . DB::getDB()->escapeString($userNameKlasse)  . "'");
	            
	            if(sizeof($schueler) > 0) DB::getDB()->query("INSERT INTO eltern_email (elternEMail, elternSchuelerAsvID, elternUserID) values('" . DB::getDB()->escapeString($userNameKlasse) . "','" . $schueler[0]->getAsvID() . "','$userID') ON DUPLICATE KEY UPDATE elternUserID=elternUserID");	            
	        }
	        
	        
	        /**
	        $eltern = DB::getDB()->query_first("SELECT * FROM eltern_email WHERE elternUserID='" . $this->data['userID'] . "'");
	        if($eltern['elternSchuelerAsvID'] != "") {
	            $this->isEltern = true;
	            $this->elternObject = new eltern($this->data['userID']);
	        } **/
	        
	        
	    }
		
	    if(DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
	    	if(DB::getSettings()->getBoolean('elternmail-create-users')) {
	    	    
	    	    // Aufräumarbeiten:
	    	    
	    	    // E-Mailadressen zuordnen zu den Benutzern, wenn schon vorhanden, aber nicht zugordnet (elternUserID=0)
	    	    DB::getDB()->query("UPDATE eltern_email SET elternUserID=(SELECT userID FROM users WHERE userName=elternEMail)");
	    	    
	    	    
	    	    // 
	    	    
	    	    
		    	include_once(PATH_LIB."/pageabstractPage.class.php");
		    	include_once(PATH_LIB."page/loginlogout/login.class.php");
		    	
	    		$users = DB::getDB()->query("SELECT * FROM users WHERE userNetwork='SCHULEINTERN_ELTERN' AND userID NOT IN (SELECT elternUserID FROM eltern_email JOIN eltern_adressen ON eltern_email.elternAdresseID=eltern_adressen.adresseID WHERE adresseWessen != 'S' AND elternUserID>0)");
	
	    		$deleteUserIDs = array();
	    		while($user = DB::getDB()->fetch_array($users)) {
	
	    			$deleteUserIDs[] = $user['userID'];
	    		}


	    		// Debugger::debugObject($deleteUserIDs,1);
	
	    		for($i = 0; $i < sizeof($deleteUserIDs); $i++) {
	    			$this->listDeleted .= $deleteUserIDs[$i] . " gelöscht.\r\n";
	    			
	    			$user = user::getUserByID($deleteUserIDs[$i]);
	    			if($user != null) $user->deleteUser();

                }

	    		// Benutzer anlegen
	    		// Keine Elternbenutzer für Schüler anlegen
                $newEltern = DB::getDB()->query("SELECT DISTINCT elternEMail FROM eltern_email JOIN eltern_adressen ON eltern_email.elternAdresseID=eltern_adressen.adresseID WHERE elternUserID=0 AND adresseWessen!='S'");

	
	    		$create = array();
	
	    		while($e = DB::getDB()->fetch_array($newEltern)) {
	    			$create[] = $e['elternEMail'];
	    		}

	    		// $this->listCreated = implode(", ", $create);return;
	
	    		$originalSubject = DB::getSettings()->getValue("elternmail-subjectnewuser");
	    		$originalText = DB::getSettings()->getValue("elternmail-textnewuser");
	
	
	
	    		for($i = 0; $i < sizeof($create); $i++) {
	
	    			$currentUser = DB::getDB()->query_first("SELECT userID FROM users WHERE userName LIKE '" . DB::getDB()->escapeString(trim($create[$i])) . "'");
	
	    			if($currentUser['userID'] > 0) {
	    				// Benutzer schon vorhanden
	    				$newID = $currentUser['userID'];			
	    			}
	    			else {
	    				$newPassword = substr(md5(rand()), 0, 8);
	    				DB::getDB()->query("INSERT INTO users (
	    						userName,
	    						userFirstName,
	    						userLastName,
	    						userCachedPasswordHash,
	    						userCachedPasswordHashTime,
	    						userLastPasswordChangeRemote,
	    						userNetwork,
	    						userEMail) values(
	    							'" . DB::getDB()->escapeString(trim($create[$i])) . "',
	    							'Eltern',
	    							'Eltern',
	    							'" . login::hash($newPassword) . "',
	    							UNIX_TIMESTAMP(),
	    							UNIX_TIMESTAMP(),
	    							'SCHULEINTERN_ELTERN',
	    							'" . DB::getDB()->escapeString(trim($create[$i]))  . "'
	    						) ON DUPLICATE KEY UPDATE userName=userName
	    				");
	
	    				$newID = DB::getDB()->insert_id();
	    				
	    				$this->listCreated .= $newID . " erstellt.\r\n";
	
	    				$schuelerSQL = DB::getDB()->query("SELECT schuelerName, schuelerRufname, schuelerKlasse FROM eltern_email JOIN schueler ON elternSchuelerAsvID=schuelerAsvID WHERE elternEMail='" . DB::getDB()->escapeString(trim($create[$i])) . "'");
	
	    				$schuelerListe = "";
	    				while($s = DB::getDB()->fetch_array($schuelerSQL)) {
	    					$schuelerListe .= $s['schuelerName'] . ", " . $s['schuelerRufname'] . " (" . $s['schuelerKlasse'] . ")\r\n";
	    				}
	
	    				$mailSubject = $originalSubject;
	
	    				$mailText = str_replace("{BENUTZERNAME}", $create[$i], $originalText);
	    				$mailText = str_replace("{PASSWORT}", $newPassword, $mailText);
	    				$mailText = str_replace("{SCHUELER}", $schuelerListe, $mailText);
	
	
	
	    				$newMail = new email(trim($create[$i]), $mailSubject, $mailText);
	    				$newMail->send();
	    			}
	
	    			DB::getDB()->query("UPDATE eltern_email SET elternUserID='" . $newID . "' WHERE elternEMail LIKE '" . DB::getDB()->escapeString(trim($create[$i])) . "'");
	
	
	    		}
	    		
	    		
	    		// Vornamen und Namen der Benutzer aktualisieren
	    		
	    		$mails = DB::getDB()->query("SELECT * FROM eltern_email WHERE elternUserID > 0");
	    		
	    		$allMailData = [];
	    		while($m = DB::getDB()->fetch_array($mails)) $allMailData[] = $m;
	    		
	    		$adressen = DB::getDB()->query("SELECT * FROM eltern_adressen WHERE adresseID IN (SELECT elternAdresseID FROM eltern_email WHERE elternUserID > 0)");
	    		$allAdressen = [];
	    		while($a = DB::getDB()->fetch_array($adressen)) $allAdressen[] = $a;
	    		
	    		// Debugger::debugObject($allAdressen,1);
	    			    		
	    		for($i = 0; $i < sizeof($allMailData); $i++) {
	    			if($allMailData[$i]['elternAdresseID'] > 0) {
	    			    for($a = 0; $a < sizeof($allAdressen); $a++) {
	    			        if($allAdressen[$a]['adresseID'] == $allMailData[$i]['elternAdresseID']) {
	    			            if($allAdressen[$a]['adresseVorname'] == "") $allAdressen[$a]['adresseVorname'] = "Eltern";
	    			            if($allAdressen[$a]['adresseFamilienname'] == "") $allAdressen[$a]['adresseFamilienname'] = "Eltern";
        	    				DB::getDB()->query("UPDATE users SET
        	    						userFirstName='" . DB::getDB()->escapeString($allAdressen[$a]['adresseVorname']) . "',
        	    						userLastName='" . DB::getDB()->escapeString($allAdressen[$a]['adresseFamilienname']) . "'
        	    						WHERE userID='" . $allMailData[$i]['elternUserID'] . "'");
        	    				break;
	    			        }
	    			    }
	    			}
	    		}
	    	}
	    }



	}
	
	public function getName() {
		return "Eltern Benutzer anlegen";
	}
	
	public function getDescription() {
		return "Legt die Elternbenutzer anhand des aktuellen ASV Imports an. Löscht nicht mehr benötigte Eltern Benutzer.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
	    if(DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
	    	if(!DB::getSettings()->getBoolean('elternmail-create-users')) {
	    		return ['success' => true, 'resultText' => 'Keine Benutzer erstellt oder gelöscht, da die Einstellung "Neue Elternbenutzer" bei den Benutzereinstellungen nicht aktiv ist,'];
	    		
	    	}
	    	else {
	    		return ['success' => $this->isSuccess, 'resultText' => 'Erstellte Benutzer: ' . (($this->listCreated != "") ? "\r\n" . $this->listCreated : "\r\nKeine") . "\r\n\r\nGelöschte Benutzer: " . (($this->listDeleted != "") ? "\r\n" . $this->listDeleted : "\r\nKeine")];
	    	}
	    }
	    else {
	    	return ['success' => true, 'resultText' => 'Keine Aktion, da Registrierungscodes verwendet werden.'];
	    	
	    }
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 7200;		// Einmal alle zwei Stunden ausführen
	}
}



?>